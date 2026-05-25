<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Support\EmailVerification;
use App\Support\Recaptcha;
use Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest', ['except' => ['logout']]);

        Recaptcha::applySiteConfig();
    }

    public function showForm()
    {
        return view('user.auth.login');
    }

    public function login(AuthRequest $request)
    {
        if (! Auth::attempt(['email' => $request->login_email, 'password' => $request->login_password])) {
            Session::flash('error', __('Email Or Password Doesn\'t Match !'));

            return redirect()->back()
                ->withInput($request->except('login_password'));
        }

        $user = Auth::user();

        if (EmailVerification::isRequired() && ! $user->hasVerifiedEmail()) {
            Auth::logout();
            $user->sendEmailVerificationNotification();

            Session::flash(
                'error',
                __('Please verify your email before logging in. A new verification link has been sent.')
            );

            return redirect()->route('verification.notice');
        }

        if ($request->has('modal')) {
            return redirect()->back();
        }

        return redirect()->intended(route('user.dashboard'));
    }

    public function logout()
    {
        Auth::logout();

        return redirect('/');
    }
}
