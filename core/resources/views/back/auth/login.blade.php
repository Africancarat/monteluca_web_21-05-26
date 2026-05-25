@extends('master.back-login')

@section('content')

        <div class="wrapper wrapper-login">
            <div class="container container-login animated fadeIn">
                <h3 class="text-center">{{ __('Sign In To Admin') }}</h3>
                <div class="login-form">

                    <form action="{{ route('back.login.submit') }}" method="POST">

                        @csrf

                        @include('alerts.alerts')

                        <div class="form-group form-floating-label emailNotRTL">
                            <input id="username" name="login_email" type="email" class="form-control input-border-bottom @error('login_email') is-invalid @enderror" value="{{ old('login_email') }}" required autocomplete="email">
                            <label for="username" class="placeholder">{{ __('Email Address') }}</label>
                            @error('login_email')
                                <p class="text-danger small mb-0 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="form-group form-floating-label rtlinput">
                            <input id="password" name="login_password" type="password" class="form-control input-border-bottom @error('login_password') is-invalid @enderror" required autocomplete="current-password">
                            <label for="password" class="placeholder">{{ __('Password') }}</label>
                            @error('login_password')
                                <p class="text-danger small mb-0 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="row justify-content-center form-sub m-0">
                            <a href="{{ route('back.forgot') }}" class="link float-right">{{ __('Forget Password ?') }}</a>
                        </div>

                        <div class="form-action mb-3">
                            <button type="submit" class="btn btn-secondary  btn-login">{{ __('Sign In') }}</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

@endsection
