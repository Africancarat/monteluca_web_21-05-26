<?php

return [
    'secret' => env('NOCAPTCHA_SECRET') ?? '',
    'sitekey' => env('NOCAPTCHA_SITEKEY') ?? '',
    // Skip widget + validation on APP_ENV=local (production keys often omit localhost).
    'bypass_on_local' => filter_var(
        env('RECAPTCHA_BYPASS_LOCAL', env('APP_ENV') === 'local'),
        FILTER_VALIDATE_BOOLEAN
    ),
    'options' => [
        'timeout' => 30,
    ],
];
