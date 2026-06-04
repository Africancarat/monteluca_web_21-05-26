<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ConsultantSlot;
use Carbon\Carbon;

class ConsultationDashboardController extends Controller
{
    public function index()
    {
        $now   = Carbon::now();
        $today = $now->toDateString();

        // ── Slot stats ──────────────────────────────────────────────────────
        $totalSlots     = ConsultantSlot::count();
        $availableSlots = ConsultantSlot::where('is_booked', false)
                            ->where('slot_date', '>=', $today)
                            ->count();
        $bookedSlots    = ConsultantSlot::where('is_booked', true)->count();

        // ── Appointment stats ────────────────────────────────────────────────
        $totalBookings   = Appointment::count();
        $virtualCount    = Appointment::where('booking_type', 'virtual')->count();
        $storeCount      = Appointment::where('booking_type', 'store_visit')->count();
        $confirmedCount  = Appointment::where('status', 'confirmed')->count();
        $cancelledCount  = Appointment::where('status', 'cancelled')->count();

        // ── Today ────────────────────────────────────────────────────────────
        $todayBookings = Appointment::whereDate('starts_at', $today)
                            ->where('status', 'confirmed')
                            ->with('slot')
                            ->orderBy('starts_at')
                            ->get();

        // ── Upcoming (next 7 days, excluding today) ──────────────────────────
        $upcomingBookings = Appointment::whereBetween('starts_at', [
                                $now->copy()->addDay()->startOfDay(),
                                $now->copy()->addDays(7)->endOfDay(),
                            ])
                            ->where('status', 'confirmed')
                            ->orderBy('starts_at')
                            ->get();

        // ── Recent bookings (last 10) ────────────────────────────────────────
        $recentBookings = Appointment::with('slot')
                            ->orderByDesc('created_at')
                            ->limit(10)
                            ->get();

        // ── Next available slot ──────────────────────────────────────────────
        $nextSlot = ConsultantSlot::where('is_booked', false)
                        ->where('slot_date', '>=', $today)
                        ->orderBy('slot_date')
                        ->orderBy('slot_time')
                        ->first();

        return view('back.consultation-dashboard', compact(
            'totalSlots', 'availableSlots', 'bookedSlots',
            'totalBookings', 'virtualCount', 'storeCount',
            'confirmedCount', 'cancelledCount',
            'todayBookings', 'upcomingBookings', 'recentBookings',
            'nextSlot'
        ));
    }
}
