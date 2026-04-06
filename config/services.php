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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Google OAuth (Socialite)
    |--------------------------------------------------------------------------
    | Konfigurasi untuk login menggunakan akun Google (SSO).
    | Nilai diambil dari file .env agar credentials tidak hardcoded di sini.
    */
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),      // Client ID dari Google Cloud Console
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),  // Client Secret dari Google Cloud Console
        'redirect'      => env('GOOGLE_REDIRECT_URI'),   // URL callback setelah user login di Google
    ],

    /*
    |--------------------------------------------------------------------------
    | Midtrans Payment Gateway
    |--------------------------------------------------------------------------
    | Cukup ganti MIDTRANS_IS_PRODUCTION=true di .env saat go-live.
    | Tidak ada yang perlu diubah di kode.
    */
    'midtrans' => [
        'server_key'    => env('MIDTRANS_SERVER_KEY'),
        'client_key'    => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

        // URL Snap JS otomatis menyesuaikan sandbox/production via env
        'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js',
    ],

];
