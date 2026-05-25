<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResendVerificationRequest;
use App\Models\User;
use App\Services\EmailVerificationService;
use App\Support\EmailVerification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class EmailVerificationController extends Controller
{
    public function __construct(
        protected EmailVerificationService $verification
    ) {
    }

    public function notice()
    {
        if (! EmailVerification::isRequired()) {
            return redirect()->route('user.login');
        }

        return view('user.auth.verification-notice');
    }

    public function verify(Request $request, int $id, string $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            Session::flash('error', __('Invalid verification link.'));

            return redirect()->route('verification.notice');
        }

        if ($user->hasVerifiedEmail()) {
            Auth::login($user);

            return redirect()->route('user.dashboard')
                ->with('success', __('Email already verified.'));
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        $this->verification->sendWelcomeTemplate($user);

        Auth::login($user);

        Session::flash('success', __('Email verified successfully. Welcome!'));

        return redirect()->route('user.dashboard');
    }

    public function resend(ResendVerificationRequest $request)
    {
        if (! EmailVerification::isRequired()) {
            return redirect()->route('user.login');
        }

        $user = User::where('email', $request->validated('email'))->first();

        if (! $user) {
            Session::flash('success', __('If that email is registered, a new verification link has been sent.'));

            return redirect()->route('verification.notice');
        }

        if ($user->hasVerifiedEmail()) {
            Session::flash('success', __('This email is already verified. You can log in.'));

            return redirect()->route('user.login');
        }

        $sent = $user->sendEmailVerificationNotification();

        Session::flash(
            $sent ? 'success' : 'error',
            $sent
                ? __('A new verification link has been sent to your email.')
                : __('We could not send the email. Please check SMTP settings or try again later.')
        );

        return redirect()->route('verification.notice');
    }
}
