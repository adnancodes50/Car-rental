<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PayFast Merchant Credentials
    |--------------------------------------------------------------------------
    |
    | Use your Sandbox or Live credentials here. For testing, PayFast provides
    | default sandbox credentials (merchant_id = 10000100, merchant_key = 46f0cd694581a).
    | You should replace these with your own sandbox/live credentials.
    |
    */

    'merchant_id'  => env('PAYFAST_MERCHANT_ID', '10000100'),
    'merchant_key' => env('PAYFAST_MERCHANT_KEY', '46f0cd694581a'),

    /*
    |--------------------------------------------------------------------------
    | PayFast Passphrase
    |--------------------------------------------------------------------------
    |
    | If you have set a passphrase in your PayFast account, you must include it
    | here. This is used for generating and verifying secure signatures.
    |
    */
    'passphrase'   => env('PAYFAST_PASSPHRASE', ''),

    /*
    |--------------------------------------------------------------------------
    | Test Mode
    |--------------------------------------------------------------------------
    |
    | When true, payments will be processed via the PayFast Sandbox.
    | Set to false when you are ready to go live.
    |
    */
    'testmode'     => env('PAYFAST_TEST_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | PayFast URLs
    |--------------------------------------------------------------------------
    |
    | These URLs are automatically set based on testmode. Sandbox should be
    | used for development and testing, while Live is used in production.
    |
    */
    'urls' => [
        'sandbox' => '	https://sandbox.payfast.co.za/eng/process
',
        'live'    => 'https://www.payfast.co.za/eng/process',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Redirect URLs
    |--------------------------------------------------------------------------
    |
    | These are optional defaults for return, cancel, and notify URLs. You can
    | override them per transaction if needed.
    |
    */
    'return_url' => env('PAYFAST_RETURN_URL', '/payment/success'),
    'cancel_url' => env('PAYFAST_CANCEL_URL', '/payment/cancel'),
    'notify_url' => env('PAYFAST_NOTIFY_URL', '/payment/notify'),
];
