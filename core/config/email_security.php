<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Block disposable / temporary email providers
    |--------------------------------------------------------------------------
    */
    'block_disposable' => env('EMAIL_BLOCK_DISPOSABLE', true),

    /*
    |--------------------------------------------------------------------------
    | Strict email validation (Laravel email rule modifiers)
    | rfc = RFC compliant format; filter = PHP filter_var; spoof = homograph domains
    | dns = MX record check (can fail on some valid mail hosts — enable in production)
    |--------------------------------------------------------------------------
    */
    'strict_modifiers' => env('EMAIL_STRICT_MODIFIERS', 'rfc,filter,spoof'),

    'validate_dns' => env('EMAIL_VALIDATE_DNS', false),

    /*
    | Extra domains to block (comma-separated in .env), e.g.:
    | EMAIL_EXTRA_BLOCKED_DOMAINS=spamdomain.com,another.net
    */
    'extra_blocked_domains' => array_filter(array_map(
        'trim',
        explode(',', (string) env('EMAIL_EXTRA_BLOCKED_DOMAINS', ''))
    )),

];
