<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'storage/logos/*'], // Only allow paths under 'api/*'

    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'], // Specify necessary HTTP methods

    'allowed_origins' => [
        'http://localhost:5173', // Example of a trusted local origin
        'https://sportma-res.onrender.com', // Example of a trusted production origin
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Authorization'], // Specify required headers

    'exposed_headers' => [],

    'max_age' => 3600, // Cache the preflight request for 1 hour

    'supports_credentials' => true,
];