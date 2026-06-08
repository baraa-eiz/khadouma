<?php

use App\Core\Env;

return [
    'name' => Env::get('APP_NAME', 'خدومة'),
    'env' => Env::get('APP_ENV', 'production'),
    'url' => Env::get('APP_URL', 'https://service.cnc-jordan.com'),
    'timezone' => Env::get('TIMEZONE', 'Asia/Damascus'),
    'language' => 'ar',
    'direction' => 'rtl',
    'paths' => [
        'root' => dirname(__DIR__),
        'storage' => dirname(__DIR__) . '/storage',
        'public' => dirname(__DIR__) . '/public',
    ],
    'admin' => [
        'session_timeout' => (int)Env::get('ADMIN_SESSION_TIMEOUT', 1800),
    ]
];
