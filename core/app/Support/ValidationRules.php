<?php

namespace App\Support;

use App\Rules\NotDisposableEmail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Reusable validation rule fragments for Phase 1 security hardening.
 */
final class ValidationRules
{
    /**
     * Laravel email rule string (RFC + filter + optional DNS MX check).
     */
    public static function strictEmailRule(): string
    {
        $modifiers = trim((string) config('email_security.strict_modifiers', 'rfc,filter,spoof'));

        if (config('email_security.validate_dns', false)) {
            $modifiers = $modifiers === '' ? 'dns' : $modifiers . ',dns';
        }

        return $modifiers === '' ? 'email' : 'email:' . $modifiers;
    }

    /**
     * Strong email validation + disposable domain blocking.
     *
     * @param  bool  $blockDisposable  Block temp-mail providers (registration, checkout, etc.)
     */
    public static function email(string $key = 'email', bool $required = true, bool $blockDisposable = true): array
    {
        $rules = ['string', 'max:255', self::strictEmailRule()];

        array_unshift($rules, $required ? 'required' : 'nullable');

        if ($blockDisposable) {
            $rules[] = new NotDisposableEmail();
        }

        return [$key => $rules];
    }

    /**
     * Strict email + optional disposable block + database unique rule.
     */
    public static function emailUnique(
        string $key,
        string $table,
        ?int $ignoreId = null,
        bool $blockDisposable = true
    ): array {
        $rules = self::email($key, true, $blockDisposable)[$key];

        $unique = Rule::unique($table, $key);
        if ($ignoreId !== null) {
            $unique->ignore($ignoreId);
        }
        $rules[] = $unique;

        return [$key => $rules];
    }

    /** Login: strict format, no disposable block (account may already exist). */
    public static function loginEmail(string $key = 'login_email'): array
    {
        return [
            $key => ['required', 'string', 'max:255', self::strictEmailRule()],
        ];
    }
    /** Honeypot field — must be empty (bot trap). */
    public static function honeypot(): array
    {
        return ['honeypot' => ['nullable', 'string', 'max:0']];
    }

    public static function personName(string $key, bool $required = true): array
    {
        $rules = ['string', 'max:100', 'regex:/^[\pL\s\-\.\']+$/u'];

        array_unshift($rules, $required ? 'required' : 'nullable');

        return [$key => $rules];
    }

    public static function password(string $key = 'password', int $min = 8, int $max = 128, bool $required = true): array
    {
        $rules = ['string', "min:{$min}", "max:{$max}"];

        array_unshift($rules, $required ? 'required' : 'nullable');

        return [$key => $rules];
    }

    /**
     * Enterprise password policy for all newly created or updated passwords.
     *
     * Keep the legacy password() helper for login/current-password checks so
     * existing users can still authenticate and then upgrade weak passwords.
     */
    public static function strongPassword(bool $required = true): array
    {
        return array_filter([
            $required ? 'required' : 'nullable',
            'string',
            'max:128',
            Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised(),
        ]);
    }

    public static function phone(string $key = 'phone', bool $required = true): array
    {
        $rules = ['string', 'digits:10'];

        array_unshift($rules, $required ? 'required' : 'nullable');

        return [$key => $rules];
    }

    /**
     * 10-digit phone + unique per row in the given table (e.g. users.phone).
     */
    public static function phoneUnique(
        string $key = 'phone',
        string $table = 'users',
        ?int $ignoreId = null,
        bool $required = true
    ): array {
        $rules = self::phone($key, $required)[$key];

        $unique = Rule::unique($table, $key);
        if ($ignoreId !== null) {
            $unique->ignore($ignoreId);
        }
        $rules[] = $unique;

        return [$key => $rules];
    }

    public static function addressLine(string $key, bool $required = true): array
    {
        $rules = ['string', 'max:255'];

        array_unshift($rules, $required ? 'required' : 'nullable');

        return [$key => $rules];
    }

    public static function zip(string $key, bool $required = true): array
    {
        $rules = ['string', 'max:20', 'regex:/^[\w\s\-]+$/'];

        array_unshift($rules, $required ? 'required' : 'nullable');

        return [$key => $rules];
    }

    public static function country(string $key, bool $required = true): array
    {
        $rules = ['string', 'max:100', 'regex:/^[\pL\s\-\.]+$/u'];

        array_unshift($rules, $required ? 'required' : 'nullable');

        return [$key => $rules];
    }

    public static function message(string $key = 'message', int $max = 2000): array
    {
        return [$key => ['required', 'string', 'max:' . $max]];
    }

    /** Product / category image upload. */
    public static function image(string $key = 'photo', bool $required = false, int $maxKb = 2048): array
    {
        $rules = ['file', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:' . $maxKb];

        array_unshift($rules, $required ? 'required' : 'nullable');

        return [$key => $rules];
    }

    public static function recaptcha(bool $enabled): array
    {
        if (! Recaptcha::isEnabled($enabled ? 1 : 0)) {
            return ['g-recaptcha-response' => ['nullable', 'string']];
        }

        return ['g-recaptcha-response' => ['required', 'string', 'captcha']];
    }
}
