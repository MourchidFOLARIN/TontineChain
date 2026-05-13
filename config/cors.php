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

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    /*
    | Si FRONTEND_URL est défini (ex. https://app.example.com), une seule origine est autorisée
    | et les cookies / en-têtes d’auth peuvent être utilisés correctement.
    | Sinon, toutes les origines sont autorisées mais sans credentials navigateur fiables.
    */
    'allowed_origins' => ($origin = env('FRONTEND_URL'))
        ? [rtrim($origin, '/')]
        : ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => (bool) env('FRONTEND_URL'),

];
