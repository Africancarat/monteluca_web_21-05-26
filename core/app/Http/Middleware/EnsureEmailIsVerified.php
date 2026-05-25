<?php

namespace App\Http\Middleware;

use App\Support\EmailVerification;
use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next, ?string $redirectToRoute = null): Response
    {
        if (! EmailVerification::isRequired()) {
            return $next($request);
        }

        if (! $request->user()
            || ($request->user() instanceof MustVerifyEmail && $request->user()->hasVerifiedEmail())) {
            return $next($request);
        }

        return $request->expectsJson()
            ? abort(403, __('Your email address is not verified.'))
            : Redirect::guest(route($redirectToRoute ?: 'verification.notice'));
    }
}
