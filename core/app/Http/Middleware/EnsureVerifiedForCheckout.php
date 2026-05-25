<?php

namespace App\Http\Middleware;

use App\Support\EmailVerification;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks logged-in checkout when email verification is required but not completed.
 * Guest checkout (is_guest_checkout) is unaffected.
 */
class EnsureVerifiedForCheckout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! EmailVerification::isRequired()) {
            return $next($request);
        }

        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return $next($request);
        }

        return Redirect::route('verification.notice')
            ->with('error', __('Please verify your email before checkout.'));
    }
}
