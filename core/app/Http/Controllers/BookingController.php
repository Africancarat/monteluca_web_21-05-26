<?php

namespace App\Http\Controllers;

use App\Jobs\SendAppointmentReminder;
use App\Mail\AppointmentAlertMail;
use App\Mail\AppointmentConfirmedMail;
use App\Models\Appointment;
use App\Models\ConsultantSlot;
use App\Services\GoogleMeetService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    // ────────────────────────────────────────────────────────────────────────
    // Pages
    // ────────────────────────────────────────────────────────────────────────

    public function index()
    {
        $storeConfig = [
            'name'    => config('services.store.name'),
            'address' => config('services.store.address'),
            'city'    => config('services.store.city'),
            'phone'   => config('services.store.phone'),
        ];

        return view('consultation', compact('storeConfig'));
    }

    // ────────────────────────────────────────────────────────────────────────
    // AJAX: available dates for date picker
    // ────────────────────────────────────────────────────────────────────────

    public function availableDates(): JsonResponse
    {
        $dates = ConsultantSlot::where('is_booked', false)
            ->where('slot_date', '>=', now()->toDateString())
            ->select('slot_date')
            ->distinct()
            ->orderBy('slot_date')
            ->pluck('slot_date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString());

        return response()->json($dates);
    }

    // ────────────────────────────────────────────────────────────────────────
    // AJAX: available slots for a given date
    // ────────────────────────────────────────────────────────────────────────

    public function slots(Request $request): JsonResponse
    {
        $request->validate(['date' => 'required|date']);

        $slots = ConsultantSlot::where('slot_date', $request->date)
            ->where('is_booked', false)
            ->orderBy('slot_time')
            ->get(['id', 'slot_time']);

        return response()->json($slots);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Store booking
    // ────────────────────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slot_id'      => 'required|integer|exists:consultant_slots,id',
            'guest_name'   => 'required|string|max:120',
            'guest_email'  => 'required|email|max:120',
            'guest_phone'  => 'required|string|max:30',
            'event_type'   => 'required|string|max:80',
            'booking_type' => 'required|in:virtual,store_visit',
            'notes'        => 'nullable|string|max:500',
        ]);

        try {
            $appointment = DB::transaction(function () use ($validated) {
            // Lock the slot row to prevent race conditions.
            // Both booking types compete for the same slot; is_booked is the
            // single gate regardless of booking_type, so a virtual booking on
            // slot 5 prevents any store visit on slot 5 and vice-versa.
            $slot = ConsultantSlot::where('id', $validated['slot_id'])
                ->where('is_booked', false)
                ->lockForUpdate()
                ->firstOrFail();

            $startsAt = Carbon::parse($slot->slot_date->toDateString() . ' ' . $slot->slot_time);

            $appointment = new Appointment();
            $appointment->slot_id     = $slot->id;
            $appointment->guest_name  = $validated['guest_name'];
            $appointment->guest_email = $validated['guest_email'];
            $appointment->guest_phone = $validated['guest_phone'];
            $appointment->event_type  = $validated['event_type'];
            $appointment->booking_type = $validated['booking_type'];
            $appointment->starts_at   = $startsAt;
            $appointment->status      = 'confirmed';
            $appointment->notes       = $validated['notes'] ?? null;

            if ($validated['booking_type'] === Appointment::TYPE_VIRTUAL) {
                // Throws (and rolls back transaction) if credentials are missing
                // or if Google Meet creation fails — slot stays unbooked.
                try {
                    $meetService = app(GoogleMeetService::class);
                    $meet = $meetService->createMeeting($appointment);
                } catch (\Throwable $e) {
                    throw new Exception('Unable to create Google Meet link: ' . $e->getMessage());
                }

                $appointment->meeting_url    = $meet['meet_url'];
                $appointment->google_event_id = $meet['event_id'];
                $appointment->store_location  = null;
            } else {
                // Store visit: no Meet link needed.
                $appointment->meeting_url     = null;
                $appointment->google_event_id = null;
                $appointment->store_location  = implode(', ', array_filter([
                    config('services.store.address'),
                    config('services.store.city'),
                ]));
            }

            $appointment->save();

            // Mark slot as booked inside the same transaction.
            $slot->is_booked = true;
            $slot->save();

            return $appointment;
        });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'This slot has just been booked. Please select another time.',
            ], 422);
        } catch (Exception $e) {
            logger()->error('Booking transaction failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Booking failed. Please try again.',
            ], 422);
        }

        // Email — customer confirmation (silent on failure).
        try {
            Mail::to($appointment->guest_email)
                ->send(new AppointmentConfirmedMail($appointment));
        } catch (Exception $e) {
            logger()->error('Customer confirmation email failed', ['error' => $e->getMessage()]);
        }

        // Email — admin alert (silent on failure).
        try {
            $adminEmail = config('services.store.admin_email');
            if ($adminEmail) {
                Mail::to($adminEmail)
                    ->send(new AppointmentAlertMail($appointment));
            }
        } catch (Exception $e) {
            logger()->error('Admin alert email failed', ['error' => $e->getMessage()]);
        }

        // WhatsApp notifications (silent on failure — never roll back booking).
        try {
            $whatsApp = app(WhatsAppService::class);

            if ($appointment->isVirtual()) {
                $whatsApp->sendConfirmation($appointment);
            } else {
                $whatsApp->sendStoreVisitConfirmation($appointment);
            }

            $whatsApp->sendConsultantAlert($appointment);
        } catch (Exception $e) {
            logger()->error('WhatsApp notification failed after booking', ['error' => $e->getMessage()]);
        }

        // Dispatch reminder jobs.
        $startsAt = $appointment->starts_at;

        $dispatch24h = $startsAt->copy()->subHours(24);
        if ($dispatch24h->isFuture()) {
            SendAppointmentReminder::dispatch($appointment, '24h')->delay($dispatch24h);
        }

        $dispatch1h = $startsAt->copy()->subHour();
        if ($dispatch1h->isFuture()) {
            SendAppointmentReminder::dispatch($appointment, '1h')->delay($dispatch1h);
        }

        return response()->json([
            'success'      => true,
            'booking_type' => $appointment->booking_type,
            'message'      => $appointment->isVirtual()
                ? 'Booking confirmed. Check your WhatsApp for your Google Meet link.'
                : 'Booking confirmed. Check your WhatsApp for your visit details and store address.',
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Cancel booking
    // ────────────────────────────────────────────────────────────────────────

    public function cancel(Request $request): JsonResponse
    {
        $request->validate(['appointment_id' => 'required|integer|exists:appointments,id']);

        $appointment = Appointment::findOrFail($request->appointment_id);

        if ($appointment->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Already cancelled.'], 422);
        }

        DB::transaction(function () use ($appointment) {
            $appointment->status = 'cancelled';
            $appointment->save();

            ConsultantSlot::where('id', $appointment->slot_id)->update(['is_booked' => false]);
        });

        // Only call Google Meet for virtual bookings that have an event ID.
        if ($appointment->isVirtual() && $appointment->google_event_id) {
            try {
                app(GoogleMeetService::class)->cancelMeeting($appointment);
            } catch (Exception $e) {
                logger()->warning('Google Meet cancel failed', ['error' => $e->getMessage()]);
            }
        }

        try {
            app(WhatsAppService::class)->sendCancellation($appointment);
        } catch (Exception $e) {
            logger()->error('WhatsApp cancellation notice failed', ['error' => $e->getMessage()]);
        }

        return response()->json(['success' => true, 'message' => 'Appointment cancelled.']);
    }
}
