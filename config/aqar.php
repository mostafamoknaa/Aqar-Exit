<?php

return [
    // Buyer pays 1.25% only when the assignment is completed. Seller pays 0.
    'buyer_commission_rate' => (float) env('AQAR_BUYER_COMMISSION_RATE', 0.0125),
    'seller_commission_rate' => 0.0,

    // Typical developer cancellation penalty used by the free exit calculator.
    'default_cancellation_percent' => (float) env('AQAR_CANCELLATION_PERCENT', 15),

    // Uploads (private documents are never exposed in the public API)
    'document_disk' => env('AQAR_DOCUMENT_DISK', 'local'),
    'image_disk' => env('FILESYSTEM_DISK', 'public'),

    // Phone OTP — provider is pluggable via AQAR_OTP_PROVIDER (log is dev-only default;
    // http calls a generic provider webhook, see OtpService). No code is hard-coded.
    'otp' => [
        'provider' => env('OTP_PROVIDER', 'log'), // log|http
        'http_endpoint' => env('OTP_HTTP_ENDPOINT'),
        'http_token' => env('OTP_API_KEY'),
        'length' => 6,
        'expiry_minutes' => (int) env('OTP_EXPIRY_MINUTES', 10),
        'max_attempts' => 5,
        'resend_cooldown_seconds' => 60,
    ],

    // FCM push — disabled safely when no project configured.
    'fcm' => [
        'project_id' => env('FCM_PROJECT_ID'),
        'server_key' => env('FCM_SERVER_KEY'), // legacy key fallback
    ],
];
