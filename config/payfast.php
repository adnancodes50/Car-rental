<?php

return [
    'merchant_id'  => env('PAYFAST_MERCHANT_ID', '10000100'),
    'merchant_key' => env('PAYFAST_MERCHANT_KEY', '46f0cd694581a'),
    'passphrase'   => env('PAYFAST_PASSPHRASE', ''),

    // true => sandbox, false => live
    'testmode'     => filter_var(env('PAYFAST_TEST_MODE', true), FILTER_VALIDATE_BOOLEAN),

    'urls' => [
        'sandbox' => 'https://sandbox.payfast.co.za/eng/process',
        'live'    => 'https://www.payfast.co.za/eng/process',
    ],

    // just paths or env overrides — NO url() here
    'return_url' => env('PAYFAST_RETURN_URL', '/payment/success'),
    'cancel_url' => env('PAYFAST_CANCEL_URL', '/payment/cancel'),
    'notify_url' => env('PAYFAST_NOTIFY_URL', '/payment/notify'),

    'valid_itn_hosts' => [
        'www.payfast.co.za',
        'sandbox.payfast.co.za',
        'w1w.payfast.co.za',
        'w2w.payfast.co.za',
    ],
];
