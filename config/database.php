<?php
/**
 * database.php
 * Khadomeh Database Configuration Loader
 * 
 * Returns database connection settings and PDO attributes as an array.
 */

if (!defined('IN_APP')) {
    define('IN_APP', true);
}

require_once __DIR__ . '/config.php';

return [
    'host' => DB_HOST,
    'dbname' => DB_NAME,
    'username' => DB_USER,
    'password' => DB_PASS,
    'charset' => DB_CHARSET,
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci"
    ]
];
