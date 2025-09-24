<?php

return [
    'stripe' => [
        'enabled' => env('STRIPE_ENABLED', false),
        'mode'    => env('STRIPE_MODE', 'sandbox'),
        'key'     => env('STRIPE_KEY'),
        'secret'  => env('STRIPE_SECRET'),
    ],
    'payfast' => [
        'enabled'      => env('PAYFAST_ENABLED', false),
        'merchant_id'  => env('PAYFAST_MERCHANT_ID'),
        'merchant_key' => env('PAYFAST_MERCHANT_KEY'),
        'passphrase'   => env('PAYFAST_PASSPHRASE'),
        'test_mode'    => env('PAYFAST_TEST_MODE', true),
        'return_url'   => env('PAYFAST_RETURN_URL'),
        'cancel_url'   => env('PAYFAST_CANCEL_URL'),
        'notify_url'   => env('PAYFAST_NOTIFY_URL'),
    ],
];

