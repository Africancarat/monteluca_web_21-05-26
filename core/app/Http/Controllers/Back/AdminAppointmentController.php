<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AdminAppointmentController extends Controller
{
    public function index(Request $request)
    {
        $appointments = Appointment::with('slot')
            ->when($request->booking_type, fn ($q, $v) => $q->where('booking_type', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->search, fn ($q, $v) => $q->where(function ($sub) use ($v) {
                $sub->where('guest_name', 'like', "%{$v}%")
                    ->orWhere('guest_email', 'like', "%{$v}%")
                    ->orWhere('guest_phone', 'like', "%{$v}%");
            }))
            ->orderByDesc('starts_at')
            ->paginate(20)
            ->withQueryString();

        return view('back.appointments.index', compact('appointments'));
    }

    public function show(Appointment $appointment)
    {
        $appointment->load('slot');

        $storeConfig = [
            'name'    => config('services.store.name'),
            'address' => config('services.store.address'),
            'city'    => config('services.store.city'),
            'phone'   => config('services.store.phone'),
        ];

        return view('back.appointments.show', compact('appointment', 'storeConfig'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status'            => 'required|in:pending,confirmed,cancelled',
            'preparation_notes' => 'nullable|string|max:2000',
            'outcome_notes'     => 'nullable|string|max:2000',
        ]);

        $appointment->update($validated);

        return redirect()
            ->route('back.appointments.show', $appointment)
            ->withSuccess('Appointment updated.');
    }
}
