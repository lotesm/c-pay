<?php

return [
    /*
    |--------------------------------------------------------------------------
    | C-Pay API Base URL
    |--------------------------------------------------------------------------
    | The base URL of the C-Pay payment gateway.
    | UAT: https://cpay-uat-env.chaperone.co.ls:5100
    | Live: set CPAY_BASE_URL in your .env
    */
    'base_url' => env('CPAY_BASE_URL', 'https://cpay-uat-env.chaperone.co.ls:5100'),

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    */
    'client_code'   => env('CPAY_CLIENT_CODE', ''),
    'api_key'       => env('CPAY_API_KEY', ''),
    'client_secret' => env('CPAY_CLIENT_SECRET', ''),
    'merchant_code' => env('CPAY_MERCHANT_CODE', ''),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */
    'currency' => env('CPAY_CURRENCY', 'LSL'),

    /*
    |--------------------------------------------------------------------------
    | Test / Sandbox Mode
    |--------------------------------------------------------------------------
    */
    'test_mode' => env('CPAY_TEST_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | HTTP Options
    |--------------------------------------------------------------------------
    */
    'timeout'    => env('CPAY_TIMEOUT', 30),
    'ssl_verify' => env('CPAY_SSL_VERIFY', true),
];
