<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for API Gateway validation.
    |
    */

    // API Key settings
    'api_key' => [
        'enabled' => env('API_SERVICE_KEY_ENABLED', true),
        'header' => env('API_SERVICE_KEY_HEADER', 'X-API-Key'),
        'value' => env('API_SERVICE_KEY'),
    ],

    // Gateway Secret settings
    'gateway_secret' => [
        'enabled' => env('API_GATEWAY_SECRET_ENABLED', true),
        'header' => env('API_GATEWAY_SECRET_HEADER', 'X-Gateway-Secret'),
        'value' => env('API_GATEWAY_SECRET'),
    ],

    // Logging settings
    'logging' => [
        'enabled' => env('API_GATEWAY_LOGGING_ENABLED', false),
        'level' => env('API_GATEWAY_LOGGING_LEVEL', 'debug'),
        'channel' => env('API_GATEWAY_LOGGING_CHANNEL', 'stack'),
    ],

    // Debug mode (only enable in non-production environments)
    'debug' => env('API_GATEWAY_DEBUG', false),
];
