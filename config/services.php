<?php

return [
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

    'semaphore' => [
        'api_key' => env('SEMAPHORE_API_KEY'),
        'sender_name' => env('SEMAPHORE_SENDER_NAME', 'CAFA'),
    ],

    // Android SMS Gateway Configuration
    'android_sms' => [
        'enabled' => env('ANDROID_SMS_GATEWAY_ENABLED', false),
        
        // For SMS-Gate.app cloud service
        'gateway_url' => env('ANDROID_SMS_GATEWAY_URL', 'https://api.sms-gate.app'),
        'username' => env('ANDROID_SMS_GATEWAY_USERNAME'),
        'password' => env('ANDROID_SMS_GATEWAY_PASSWORD'),
        'device_id' => env('ANDROID_SMS_GATEWAY_DEVICE_ID'),
        
        // For local Android gateway (alternative)
        'api_token' => env('ANDROID_SMS_GATEWAY_TOKEN'),
        
        'timeout' => env('ANDROID_SMS_GATEWAY_TIMEOUT', 30),
    ],

    // SMS Provider Configuration
    'sms' => [
        'provider' => env('SMS_PROVIDER', 'semaphore'), // Options: android, semaphore, hybrid
        'fallback_enabled' => env('SMS_FALLBACK_ENABLED', true),
        'max_retries' => env('SMS_MAX_RETRIES', 3),
    ],
];