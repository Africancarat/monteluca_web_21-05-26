<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;

/**
 * reCAPTCHA is toggled in admin (settings.recaptcha).
 * Production keys often omit localhost — bypass on local dev when configured.
 */
final class Recaptcha
{
    public static function isEnabled($recaptchaSetting = null): bool
    {
        if ($recaptchaSetting === null) {
            $recaptchaSetting = (int) (Setting::first()->recaptcha ?? 0);
        } else {
            $recaptchaSetting = (int) $recaptchaSetting;
        }

        if ($recaptchaSetting !== 1) {
            return false;
        }

        if (config('captcha.bypass_on_local') && app()->environment('local')) {
            return false;
        }

        return true;
    }

    public static function applySiteConfig(?Setting $setting = null): void
    {
        $setting ??= Setting::first();

        if (! $setting || ! self::isEnabled($setting->recaptcha)) {
            return;
        }

        Config::set('captcha.sitekey', $setting->google_recaptcha_site_key);
        Config::set('captcha.secret', $setting->google_recaptcha_secret_key);
    }
}
