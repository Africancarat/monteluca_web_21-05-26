<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Repositories\Front\UserRepository;
use App\Support\EmailVerification;
use App\Support\Recaptcha;
use Illuminate\Support\Facades\Session;

class RegisterController extends Controller
{
    public function __construct(
        protected UserRepository $repository
    ) {
        Recaptcha::applySiteConfig();
    }

    public function showForm()
    {
        return view('user.auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $this->repository->register($request);

        if (! EmailVerification::isRequired()) {
            Session::flash('success', __('Account registered successfully. Please log in.'));

            return redirect()->route('user.login');
        }

        Session::flash(
            'success',
            __('Account created. Please check your email and click the verification link to activate your account.')
        );

        return redirect()->route('verification.notice');
    }

    /** @deprecated Legacy OTP link redirects to the signed-link flow. */
    public function verify($token)
    {
        Session::flash(
            'error',
            __('Please use the verification link sent to your email. You can request a new link below.')
        );

        return redirect()->route('verification.notice');
    }
}
