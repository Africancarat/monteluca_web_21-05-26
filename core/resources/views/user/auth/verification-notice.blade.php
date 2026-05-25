@extends('master.front')
@section('title')
    {{ __('Verify Email') }}
@endsection
@section('content')
    <div class="page-title">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <ul class="breadcrumbs">
                        <li><a href="{{ route('front.index') }}">{{ __('Home') }}</a></li>
                        <li class="separator"></li>
                        <li>{{ __('Verify Email') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="container padding-bottom-3x mb-1">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @include('alerts.alerts')

                <div class="card auth-form-card">
                    <div class="card-body">
                        <h4 class="text-center margin-bottom-1x">{{ __('Verify your email') }}</h4>
                        <p class="text-muted text-center">
                            {{ __('We sent a secure verification link to your inbox. Click the link to activate your account. The link expires after a limited time.') }}
                        </p>

                        <form method="post" action="{{ route('verification.send') }}" class="mt-4">
                            @csrf
                            <div class="form-group">
                                <label for="resend-email">{{ __('Email address') }}</label>
                                <input class="form-control @error('email') is-invalid @enderror" type="email"
                                    name="email" id="resend-email" value="{{ old('email') }}" required
                                    autocomplete="email" placeholder="{{ __('Enter your registered email') }}">
                                @error('email')
                                    <p class="text-danger mb-0">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Resend verification email') }}
                                </button>
                            </div>
                        </form>

                        <p class="text-center mt-4 mb-0">
                            <a class="text-base-color" href="{{ route('user.login') }}">{{ __('Back to login') }}</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
