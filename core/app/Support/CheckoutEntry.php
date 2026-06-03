<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

final class CheckoutEntry
{
    /**
     * URL for cart / header "Checkout" — guests go to login; logged-in users go to checkout.
     */
    public static function url(): string
    {
        if (! Auth::check()) {
            return route('user.login', ['redirect' => 'checkout']);
        }

        $setting = Setting::first();

        return (int) ($setting->is_single_checkout ?? 0) === 1
            ? route('front.checkout')
            : route('front.checkout.billing');
    }
}
