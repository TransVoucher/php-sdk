<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TransVoucher API Configuration
    |--------------------------------------------------------------------------
    */

    // API Key from your TransVoucher dashboard
    'api_key' => env('TRANSVOUCHER_API_KEY'),

    // API Secret from your TransVoucher dashboard
    'api_secret' => env('TRANSVOUCHER_API_SECRET'),

    // Environment: 'sandbox' or 'production'
    'environment' => env('TRANSVOUCHER_ENVIRONMENT', 'sandbox'),

    // Optional custom API URL (overrides the default URL for the environment)
    'api_url' => env('TRANSVOUCHER_API_URL'),
];