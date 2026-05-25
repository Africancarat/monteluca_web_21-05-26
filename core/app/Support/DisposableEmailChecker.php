<?php

namespace App\Support;

/**
 * Detects disposable / blocked email domains for registration and checkout.
 */
final class DisposableEmailChecker
{
    /** @var array<string, true>|null */
    private static ?array $blockedDomains = null;

    public static function extractDomain(string $email): ?string
    {
        $email = strtolower(trim($email));
        $at = strrpos($email, '@');

        if ($at === false || $at === strlen($email) - 1) {
            return null;
        }

        $domain = substr($email, $at + 1);

        return $domain !== '' ? $domain : null;
    }

    public static function isBlocked(string $email): bool
    {
        if (! config('email_security.block_disposable', true)) {
            return false;
        }

        $domain = self::extractDomain($email);

        if ($domain === null) {
            return true;
        }

        $blocked = self::blockedDomainMap();

        if (isset($blocked[$domain])) {
            return true;
        }

        // Block subdomains of listed providers (e.g. abc.mailinator.com).
        $parts = explode('.', $domain);
        for ($i = 1, $len = count($parts); $i < $len; $i++) {
            $parent = implode('.', array_slice($parts, $i));
            if (isset($blocked[$parent])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, true>
     */
    private static function blockedDomainMap(): array
    {
        if (self::$blockedDomains !== null) {
            return self::$blockedDomains;
        }

        $domains = array_merge(
            config('disposable_email_domains', []),
            config('email_security.extra_blocked_domains', [])
        );

        $file = storage_path('app/security/blocked-email-domains.txt');
        if (is_readable($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            foreach ($lines as $line) {
                $line = strtolower(trim($line));
                if ($line !== '' && ! str_starts_with($line, '#')) {
                    $domains[] = $line;
                }
            }
        }

        $map = [];
        foreach ($domains as $domain) {
            $domain = strtolower(trim((string) $domain));
            if ($domain !== '') {
                $map[$domain] = true;
            }
        }

        self::$blockedDomains = $map;

        return self::$blockedDomains;
    }

    /** Clear cached blocklist (useful in tests). */
    public static function clearCache(): void
    {
        self::$blockedDomains = null;
    }
}
