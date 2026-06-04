<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'demo' => [
        'enabled' => false,
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'service_account_json' => storage_path('app/google-service-account.json'),
        'calendar_id'          => env('GOOGLE_CALENDAR_ID', 'primary'),
    ],

    'twilio' => [
        'sid'   => env('TWILIO_SID', ''),
        'token' => env('TWILIO_AUTH_TOKEN', ''),
        'from'  => env('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886'),
    ],

    'store' => [
        'name'              => env('STORE_NAME', 'African Carat'),
        'address'           => env('STORE_ADDRESS', ''),
        'city'              => env('STORE_CITY', ''),
        'phone'             => env('STORE_PHONE', ''),
        'email'             => env('STORE_EMAIL', ''),
        'consultant_phone'  => env('CONSULTANT_WHATSAPP', ''),
        'admin_email'       => env('BOOKING_ADMIN_EMAIL', ''),
    ],

];
