<?php

return [
    'mode' => env('PAYPAL_MODE', 'sandbox'),
    'client_id' => env('PAYPAL_CLIENT_ID'),
    'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
    'brand_name' => env('PAYPAL_BRAND_NAME', env('APP_NAME', '360Cars')),
    'currency' => env('PAYPAL_CURRENCY', 'USD'),
    'return_url' => env('PAYPAL_RETURN_URL'),
    'cancel_url' => env('PAYPAL_CANCEL_URL'),
    'base_urls' => [
        'sandbox' => 'https://api-m.sandbox.paypal.com',
        'live' => 'https://api-m.paypal.com',
    ],
];
