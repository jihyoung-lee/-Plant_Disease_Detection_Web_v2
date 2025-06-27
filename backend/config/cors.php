<?php


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
return [

    'paths' => ['api/*', 'login', 'logout', 'register', 'email/*', 'user'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization', // 대문자 A
        'authorization', // 소문자 a 도 추가
        'Accept',
        'Origin',
    ],
    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];

