<?php

return [

    'driver' => env('SESSION_DRIVER', 'redis'),

    'lifetime' => 120,

    'expire_on_close' => false,

    'encrypt' => true,

    'files' => storage_path().'/framework/sessions',

    'connection' => env('SESSION_CONNECTION', null),

    'table' => 'sessions',

    'lottery' => [2, 100],

    'cookie' => env('SESSION_COOKIE_NAME', 'laravel_session'),

    'path' => '/',

    'domain' => env('APP_DOMAIN', null),

    'secure' => env('SESSION_SECURE_COOKIE', env('APP_ENV') !== 'local'),

    'http_only' => true,

    'same_site' => 'lax',

];
