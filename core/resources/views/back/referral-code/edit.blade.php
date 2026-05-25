@extends('master.back')

@section('content')
<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h3 class="mb-0 bc-title"><b>{{ __('Edit Referral Code') }}</b></h3>
                <a class="btn btn-primary btn-sm" href="{{ route('back.referral-code.index') }}">
                    <i class="fas fa-chevron-left"></i> {{ __('Back') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card o-hidden border-0 shadow-lg">
                <div class="card-body">
                    <form class="admin-form" action="{{ route('back.referral-code.update', $data->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('alerts.alerts')
                        @include('back.referral-code.form', ['data' => $data])
                        <div class="form-group">
                            <button type="submit" class="btn btn-secondary">{{ __('Submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
