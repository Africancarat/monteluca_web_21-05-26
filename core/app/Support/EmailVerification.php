<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Whether signed email verification is required (admin setting).
 */
final class EmailVerification
{
    public static function isRequired(): bool
    {
        $setting = Setting::first();

        return $setting && (int) $setting->is_mail_verify === 1;
    }
}
