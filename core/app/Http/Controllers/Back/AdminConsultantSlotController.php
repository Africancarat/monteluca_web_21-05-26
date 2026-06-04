<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\ConsultantSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminConsultantSlotController extends Controller
{
    public function index(Request $request)
    {
        $slots = ConsultantSlot::when($request->date, fn ($q, $v) => $q->where('slot_date', $v))
            ->when($request->status === 'available', fn ($q) => $q->where('is_booked', false))
            ->when($request->status === 'booked',    fn ($q) => $q->where('is_booked', true))
            ->orderBy('slot_date')
            ->orderBy('slot_time')
            ->paginate(30)
            ->withQueryString();

        return view('back.slots.index', compact('slots'));
    }

    public function create()
    {
        return view('back.slots.create');
    }

    public function store(Request $request)
    {
        // Strip empty custom-time inputs before validation runs
        $request->merge([
            'times' => array_values(array_filter(
                (array) $request->input('times', []),
                fn ($t) => trim((string) $t) !== ''
            )),
        ]);

        $request->validate([
            'date_from'       => 'required|date',
            'date_to'         => 'required|date|after_or_equal:date_from',
            'available_days'  => 'required|array|min:1',
            'available_days.*'=> 'integer|between:0,6',
            'times'           => 'required|array|min:1',
            'times.*'         => ['string', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
        ], [
            'available_days.required' => 'Please select at least one available day.',
            'available_days.min'      => 'Please select at least one available day.',
            'times.required'          => 'Please select at least one time slot.',
            'times.min'               => 'Please select at least one time slot.',
        ]);

        $from          = Carbon::parse($request->date_from)->startOfDay();
        $to            = Carbon::parse($request->date_to)->startOfDay();
        $availableDays = array_map('intval', $request->available_days);

        // Normalise each time to H:i:s — simple string padding, no Carbon needed
        $times = collect($request->times)
            ->map(function (string $t): string {
                $t = trim($t);
                // Pad single-digit hours: "9:00" → "09:00:00", "10:00" → "10:00:00"
                [$h, $m] = explode(':', $t);
                return str_pad((int) $h, 2, '0', STR_PAD_LEFT) . ':' . $m . ':00';
            })
            ->unique()
            ->values()
            ->toArray();

        $created = 0;
        $skipped = 0;
        $current = $from->copy();

        while ($current->lte($to)) {
            if (in_array($current->dayOfWeek, $availableDays, true)) {
                foreach ($times as $time) {
                    $exists = ConsultantSlot::where('slot_date', $current->toDateString())
                        ->where('slot_time', $time)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                    } else {
                        ConsultantSlot::create([
                            'slot_date' => $current->toDateString(),
                            'slot_time' => $time,
                            'is_booked' => false,
                        ]);
                        $created++;
                    }
                }
            }
            $current->addDay();
        }

        if ($created === 0 && $skipped === 0) {
            $dayNames   = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
            $selected   = implode(', ', array_map(fn ($d) => $dayNames[$d], $availableDays));
            return back()
                ->withInput()
                ->withErrors(['days_mismatch' =>
                    "No slots created. The days you selected ({$selected}) do not fall " .
                    "within {$from->toDateString()} → {$to->toDateString()}. " .
                    "Widen the date range or select the correct days."
                ]);
        }

        $msg = "Created {$created} slot(s).";
        if ($skipped) $msg .= " {$skipped} duplicate(s) skipped.";

        return redirect()->route('back.slots.index')->withSuccess($msg);
    }

    public function destroy(ConsultantSlot $slot)
    {
        if ($slot->is_booked) {
            return back()->withErrors(['delete' => 'Cannot delete a booked slot. Cancel the appointment first.']);
        }
        $slot->delete();
        return back()->withSuccess('Slot deleted.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:consultant_slots,id',
        ]);

        $slots   = ConsultantSlot::whereIn('id', $request->ids)->get();
        $deleted = 0;
        $skipped = 0;

        foreach ($slots as $slot) {
            if ($slot->is_booked) {
                $skipped++;
            } else {
                $slot->delete();
                $deleted++;
            }
        }

        $msg = "Deleted {$deleted} slot(s).";
        if ($skipped) $msg .= " {$skipped} booked slot(s) were skipped.";
        return back()->withSuccess($msg);
    }
}
