<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | VAPID Configuration
    |--------------------------------------------------------------------------
    |
    | VAPID (Voluntary Application Server Identification) keys are required
    | for Web Push notifications. Generate them with:
    | ./vendor/bin/webpush generate:vapid
    |
    */

    'vapid' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:support@inte.team'),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Defaults
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'icon' => '/icon-192x192.png',
        'badge' => '/icon-192x192.png',
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */

    'queue' => [
        'connection' => env('PUSH_QUEUE_CONNECTION', 'redis'),
        'queue' => env('PUSH_QUEUE_NAME', 'push-notifications'),
    ],
];
