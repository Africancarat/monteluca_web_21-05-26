@extends('master.back')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="card mb-4">
        <div class="card-body d-sm-flex align-items-center justify-content-between">
            <h3 class="mb-0 bc-title"><b>Consultation Dashboard</b></h3>
            <div style="display:flex;gap:8px;">
                <a href="{{ route('back.slots.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-calendar-plus"></i> Open Slots
                </a>
                <a href="{{ route('back.appointments.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-list"></i> All Appointments
                </a>
            </div>
        </div>
    </div>

    {{-- ── Slot stats ── --}}
    <div class="row mb-2">
        <div class="col-12"><h6 class="text-muted text-uppercase" style="font-size:11px;letter-spacing:.08em;">Slot availability</h6></div>

        <div class="col-md-4 mb-3">
            <div class="card shadow h-100">
                <div class="card-body d-flex align-items-center" style="gap:16px;">
                    <div style="width:52px;height:52px;border-radius:50%;background:#28a745;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-calendar-check" style="color:#fff;font-size:20px;"></i>
                    </div>
                    <div>
                        <div style="font-size:28px;font-weight:700;line-height:1;">{{ $availableSlots }}</div>
                        <div class="text-muted" style="font-size:13px;">Available slots</div>
                    </div>
                    <a href="{{ route('back.slots.index') }}?status=available" class="stretched-link"></a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow h-100">
                <div class="card-body d-flex align-items-center" style="gap:16px;">
                    <div style="width:52px;height:52px;border-radius:50%;background:#dc3545;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-calendar-times" style="color:#fff;font-size:20px;"></i>
                    </div>
                    <div>
                        <div style="font-size:28px;font-weight:700;line-height:1;">{{ $bookedSlots }}</div>
                        <div class="text-muted" style="font-size:13px;">Booked slots</div>
                    </div>
                    <a href="{{ route('back.slots.index') }}?status=booked" class="stretched-link"></a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card shadow h-100">
                <div class="card-body d-flex align-items-center" style="gap:16px;">
                    <div style="width:52px;height:52px;border-radius:50%;background:#6c757d;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-calendar" style="color:#fff;font-size:20px;"></i>
                    </div>
                    <div>
                        <div style="font-size:28px;font-weight:700;line-height:1;">{{ $totalSlots }}</div>
                        <div class="text-muted" style="font-size:13px;">Total slots created</div>
                    </div>
                    <a href="{{ route('back.slots.index') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Booking stats ── --}}
    <div class="row mb-2">
        <div class="col-12"><h6 class="text-muted text-uppercase" style="font-size:11px;letter-spacing:.08em;">Bookings</h6></div>

        <div class="col-md-3 mb-3">
            <div class="card shadow h-100">
                <div class="card-body d-flex align-items-center" style="gap:16px;">
                    <div style="width:52px;height:52px;border-radius:50%;background:#007bff;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-book-open" style="color:#fff;font-size:18px;"></i>
                    </div>
                    <div>
                        <div style="font-size:28px;font-weight:700;line-height:1;">{{ $totalBookings }}</div>
                        <div class="text-muted" style="font-size:13px;">Total bookings</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow h-100">
                <div class="card-body d-flex align-items-center" style="gap:16px;">
                    <div style="width:52px;height:52px;border-radius:50%;background:#17a2b8;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-video" style="color:#fff;font-size:18px;"></i>
                    </div>
                    <div>
                        <div style="font-size:28px;font-weight:700;line-height:1;">{{ $virtualCount }}</div>
                        <div class="text-muted" style="font-size:13px;">Virtual</div>
                    </div>
                    <a href="{{ route('back.appointments.index') }}?booking_type=virtual" class="stretched-link"></a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow h-100">
                <div class="card-body d-flex align-items-center" style="gap:16px;">
                    <div style="width:52px;height:52px;border-radius:50%;background:#28a745;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-store" style="color:#fff;font-size:18px;"></i>
                    </div>
                    <div>
                        <div style="font-size:28px;font-weight:700;line-height:1;">{{ $storeCount }}</div>
                        <div class="text-muted" style="font-size:13px;">Store visits</div>
                    </div>
                    <a href="{{ route('back.appointments.index') }}?booking_type=store_visit" class="stretched-link"></a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow h-100">
                <div class="card-body d-flex align-items-center" style="gap:16px;">
                    <div style="width:52px;height:52px;border-radius:50%;background:#dc3545;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-times-circle" style="color:#fff;font-size:18px;"></i>
                    </div>
                    <div>
                        <div style="font-size:28px;font-weight:700;line-height:1;">{{ $cancelledCount }}</div>
                        <div class="text-muted" style="font-size:13px;">Cancelled</div>
                    </div>
                    <a href="{{ route('back.appointments.index') }}?status=cancelled" class="stretched-link"></a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Next available slot callout ── --}}
    @if($nextSlot)
    <div class="alert alert-success d-flex align-items-center mb-4" style="gap:12px;">
        <i class="fas fa-clock" style="font-size:18px;"></i>
        <span>
            Next available slot:
            <strong>{{ \Carbon\Carbon::parse($nextSlot->slot_date)->format('D d M Y') }}</strong>
            at <strong>{{ \Carbon\Carbon::parse($nextSlot->slot_time)->format('H:i') }}</strong>
        </span>
    </div>
    @else
    <div class="alert alert-warning mb-4">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        No available slots. <a href="{{ route('back.slots.create') }}"><strong>Open new slots</strong></a> so customers can book.
    </div>
    @endif

    <div class="row">

        {{-- ── Today's appointments ── --}}
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <b>Today's appointments</b>
                    <span class="badge badge-primary">{{ $todayBookings->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($todayBookings->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">No appointments today.</p>
                    @else
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Time</th><th>Guest</th><th>Type</th><th></th></tr></thead>
                        <tbody>
                            @foreach($todayBookings as $appt)
                            <tr>
                                <td>{{ $appt->starts_at->format('H:i') }}</td>
                                <td>
                                    <strong>{{ $appt->guest_name }}</strong><br>
                                    <small class="text-muted">{{ $appt->guest_phone }}</small>
                                </td>
                                <td>
                                    @if($appt->booking_type === 'virtual')
                                        <span class="badge badge-primary">Virtual</span>
                                    @else
                                        <span class="badge badge-success">Store</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('back.appointments.show', $appt) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Upcoming this week ── --}}
        <div class="col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <b>Upcoming — next 7 days</b>
                    <span class="badge badge-secondary">{{ $upcomingBookings->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($upcomingBookings->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">No upcoming appointments.</p>
                    @else
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Date & Time</th><th>Guest</th><th>Type</th><th></th></tr></thead>
                        <tbody>
                            @foreach($upcomingBookings as $appt)
                            <tr>
                                <td>
                                    {{ $appt->starts_at->format('D d M') }}<br>
                                    <small class="text-muted">{{ $appt->starts_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $appt->guest_name }}</strong><br>
                                    <small class="text-muted">{{ $appt->event_type }}</small>
                                </td>
                                <td>
                                    @if($appt->booking_type === 'virtual')
                                        <span class="badge badge-primary">Virtual</span>
                                    @else
                                        <span class="badge badge-success">Store</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('back.appointments.show', $appt) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Recent bookings ── --}}
        <div class="col-12 mb-4">
            <div class="card shadow">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <b>Recent bookings</b>
                    <a href="{{ route('back.appointments.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Guest</th>
                                <th>Phone</th>
                                <th>Consultation</th>
                                <th>Type</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBookings as $appt)
                            <tr>
                                <td>{{ $appt->id }}</td>
                                <td>{{ $appt->guest_name }}</td>
                                <td>{{ $appt->guest_phone }}</td>
                                <td>{{ $appt->event_type }}</td>
                                <td>
                                    @if($appt->booking_type === 'virtual')
                                        <span class="badge badge-primary">Virtual</span>
                                    @else
                                        <span class="badge badge-success">Store visit</span>
                                    @endif
                                </td>
                                <td>{{ $appt->starts_at ? $appt->starts_at->format('d M Y, H:i') : '—' }}</td>
                                <td>
                                    @if($appt->status === 'confirmed')
                                        <span class="badge badge-success">Confirmed</span>
                                    @elseif($appt->status === 'cancelled')
                                        <span class="badge badge-danger">Cancelled</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('back.appointments.show', $appt) }}" class="btn btn-xs btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center text-muted py-3">No bookings yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
