@extends('master.back')

@section('content')

<div class="container-fluid">

    {{-- Page Heading --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h3 class="mb-0 bc-title"><b>{{ __('Appointments') }}</b></h3>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('back.appointments.index') }}" class="form-inline flex-wrap" style="gap:10px;">
                <input type="text"
                       name="search"
                       class="form-control form-control-sm"
                       placeholder="{{ __('Search name / email / phone') }}"
                       value="{{ request('search') }}"
                       style="min-width:200px;">

                <select name="booking_type" class="form-control form-control-sm">
                    <option value="">{{ __('All booking types') }}</option>
                    <option value="virtual"     {{ request('booking_type') === 'virtual'     ? 'selected' : '' }}>{{ __('Virtual') }}</option>
                    <option value="store_visit" {{ request('booking_type') === 'store_visit' ? 'selected' : '' }}>{{ __('Store visit') }}</option>
                </select>

                <select name="status" class="form-control form-control-sm">
                    <option value="">{{ __('All statuses') }}</option>
                    <option value="confirmed"  {{ request('status') === 'confirmed'  ? 'selected' : '' }}>{{ __('Confirmed') }}</option>
                    <option value="pending"    {{ request('status') === 'pending'    ? 'selected' : '' }}>{{ __('Pending') }}</option>
                    <option value="cancelled"  {{ request('status') === 'cancelled'  ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                </select>

                <button type="submit" class="btn btn-primary btn-sm">{{ __('Filter') }}</button>
                <a href="{{ route('back.appointments.index') }}" class="btn btn-secondary btn-sm">{{ __('Reset') }}</a>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card shadow mb-4">
        <div class="card-body">
            @include('alerts.alerts')
            <div class="gd-responsive-table">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Guest') }}</th>
                            <th>{{ __('Booking type') }}</th>
                            <th>{{ __('Consultation') }}</th>
                            <th>{{ __('Date & time') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appt)
                        <tr>
                            <td>{{ $appt->id }}</td>
                            <td>
                                <strong>{{ $appt->guest_name }}</strong><br>
                                <small class="text-muted">{{ $appt->guest_email }}</small><br>
                                <small class="text-muted">{{ $appt->guest_phone }}</small>
                            </td>
                            <td>
                                @if($appt->booking_type === 'virtual')
                                    <span class="badge badge-primary">{{ __('Virtual') }}</span>
                                @else
                                    <span class="badge badge-success">{{ __('Store visit') }}</span>
                                @endif
                            </td>
                            <td>{{ $appt->event_type }}</td>
                            <td>{{ $appt->starts_at ? $appt->starts_at->format('D d M Y, H:i') : '—' }}</td>
                            <td>
                                @if($appt->status === 'confirmed')
                                    <span class="badge badge-success">{{ __('Confirmed') }}</span>
                                @elseif($appt->status === 'cancelled')
                                    <span class="badge badge-danger">{{ __('Cancelled') }}</span>
                                @else
                                    <span class="badge badge-warning">{{ __('Pending') }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('back.appointments.show', $appt) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> {{ __('View') }}
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">{{ __('No appointments found.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $appointments->links() }}
            </div>
        </div>
    </div>

</div>

@endsection
