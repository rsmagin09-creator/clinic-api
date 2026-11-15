<?php

/*
 * Настройки CORS для веб-клиента (ПР11).
 * Клиент работает на отдельном адресе (CLIENT_URL в .env, по умолчанию http://127.0.0.1:5500).
 * Авторизация по Bearer-токену, поэтому cookie не передаются: supports_credentials = false.
 */
return [

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_filter(array_map('trim', explode(',', env('CLIENT_URL', 'http://127.0.0.1:5500')))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Accept', 'Content-Type', 'Authorization', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => false,

];
