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

    // 추가: 회원가입 API 호출을 위한 '/register' 경로 허용
    'paths' => ['api/*', 'login', 'logout', 'register', 'sanctum/csrf-cookie', 'email/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:5174', 'http://127.0.0.1:5174'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];

