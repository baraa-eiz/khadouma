<?php

use App\Core\Env;

return [
    'host' => Env::get('DB_HOST', '127.0.0.1'),
    'port' => Env::get('DB_PORT', '3306'),
    'dbname' => Env::get('DB_NAME', 'khadomeh'),
    'username' => Env::get('DB_USER', 'khadomeh'),
    'password' => Env::get('DB_PASS', 'kH3d0M3h_db_p@ss_2026!'),
    'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
];
