@extends('master.back')

@section('content')

<div class="container-fluid">

    {{-- Heading --}}
    <div class="card mb-4">
        <div class="card-body d-sm-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center" style="gap:12px;">
                <h3 class="mb-0 bc-title">
                    <b>{{ __('Appointment') }} #{{ $appointment->id }}</b>
                </h3>
                @if($appointment->booking_type === 'virtual')
                    <span class="badge badge-primary" style="font-size:13px;">{{ __('Virtual') }}</span>
                @else
                    <span class="badge badge-success" style="font-size:13px;">{{ __('Store visit') }}</span>
                @endif
                @if($appointment->status === 'confirmed')
                    <span class="badge badge-success" style="font-size:13px;">{{ __('Confirmed') }}</span>
                @elseif($appointment->status === 'cancelled')
                    <span class="badge badge-danger" style="font-size:13px;">{{ __('Cancelled') }}</span>
                @else
                    <span class="badge badge-warning" style="font-size:13px;">{{ __('Pending') }}</span>
                @endif
            </div>
            <a href="{{ route('back.appointments.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> {{ __('Back to list') }}
            </a>
        </div>
    </div>

    <div class="row">

        {{-- Left: Guest + booking details --}}
        <div class="col-lg-7 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="mb-3">{{ __('Booking details') }}</h5>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="35%">{{ __('Guest') }}</th>
                            <td>{{ $appointment->guest_name }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Email') }}</th>
                            <td>{{ $appointment->guest_email }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Phone') }}</th>
                            <td>{{ $appointment->guest_phone }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Consultation type') }}</th>
                            <td>{{ $appointment->event_type }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Booking type') }}</th>
                            <td>
                                @if($appointment->booking_type === 'virtual')
                                    <span class="badge badge-primary">{{ __('Virtual') }}</span>
                                @else
                                    <span class="badge badge-success">{{ __('Store visit') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <td>{{ $appointment->starts_at ? $appointment->starts_at->format('D d M Y') : '—' }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Time') }}</th>
                            <td>{{ $appointment->starts_at ? $appointment->starts_at->format('H:i') : '—' }}</td>
                        </tr>
                        @if($appointment->isVirtual() && $appointment->meeting_url)
                        <tr>
                            <th>{{ __('Google Meet') }}</th>
                            <td>
                                <a href="{{ $appointment->meeting_url }}" target="_blank" class="btn btn-primary btn-sm">
                                    <i class="fas fa-video"></i> {{ __('Join Meet') }}
                                </a>
                            </td>
                        </tr>
                        @endif
                        @if($appointment->isStoreVisit())
                        <tr>
                            <th>{{ __('Store address') }}</th>
                            <td>
                                <strong>{{ $storeConfig['name'] }}</strong><br>
                                {{ $storeConfig['address'] }}{{ $storeConfig['city'] ? ', ' . $storeConfig['city'] : '' }}<br>
                                @if($storeConfig['phone'])
                                <small class="text-muted">{{ $storeConfig['phone'] }}</small>
                                @endif
                                @if($appointment->store_location)
                                <br><small class="text-muted">{{ $appointment->store_location }}</small>
                                @endif
                            </td>
                        </tr>
                        @endif
                        @if($appointment->notes)
                        <tr>
                            <th>{{ __('Customer notes') }}</th>
                            <td>{{ $appointment->notes }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Right: Update form --}}
        <div class="col-lg-5 mb-4">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="mb-3">{{ __('Update appointment') }}</h5>
                    <form method="POST" action="{{ route('back.appointments.update', $appointment) }}">
                        @csrf
                        @method('PUT')
                        @include('alerts.alerts')

                        <div class="form-group">
                            <label>{{ __('Status') }}</label>
                            <select name="status" class="form-control">
                                <option value="confirmed" {{ $appointment->status === 'confirmed' ? 'selected' : '' }}>{{ __('Confirmed') }}</option>
                                <option value="pending"   {{ $appointment->status === 'pending'   ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                <option value="cancelled" {{ $appointment->status === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>{{ __('Preparation notes') }}</label>
                            <textarea name="preparation_notes" class="form-control" rows="4"
                                placeholder="{{ __('Things to prepare for this appointment…') }}">{{ old('preparation_notes', $appointment->preparation_notes) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label>{{ __('Outcome notes') }}</label>
                            <textarea name="outcome_notes" class="form-control" rows="4"
                                placeholder="{{ __('What was discussed / decided…') }}">{{ old('outcome_notes', $appointment->outcome_notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save"></i> {{ __('Save') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection
