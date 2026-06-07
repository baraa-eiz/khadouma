<?php
/**
 * global config.php
 * Khadomeh Application Configuration
 * 
 * This file defines all system-wide configuration constants.
 * It detects the environment dynamically to facilitate seamless local
 * development in XAMPP and production hosting on the VPS.
 */

// Bootstrapping verification
if (!defined('IN_APP')) {
    define('IN_APP', true);
}

// Set timezone to Syria/Damascus (or UTC depending on preference)
date_default_timezone_set('Asia/Damascus');

// Dynamic environment detection
$isLocal = false;
if (isset($_SERVER['HTTP_HOST'])) {
    $host = $_SERVER['HTTP_HOST'];
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
        $isLocal = true;
    }
}

// General Application Settings
define('APP_NAME', 'خدومة');
define('APP_ENV', $isLocal ? 'development' : 'production');
define('APP_URL', $isLocal ? 'http://localhost/khadomeh' : 'https://service.cnc-jordan.com');
define('APP_DIR', dirname(__DIR__));

// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_NAME', $isLocal ? 'khadomeh_db' : 'khadomeh');
define('DB_USER', $isLocal ? 'root' : 'khadomeh');
define('DB_PASS', $isLocal ? '' : 'kH3d0M3h_db_p@ss_2026!');
define('DB_CHARSET', 'utf8mb4');

// Localization Settings
define('DEFAULT_LANGUAGE', 'ar');
define('DEFAULT_DIRECTION', 'rtl');
define('TIMEZONE', 'Asia/Damascus');

// Path Constants
define('UPLOAD_PATH', APP_DIR . '/public/assets/images/uploads');
define('PLACEHOLDER_IMAGE_PATH', APP_DIR . '/public/assets/images/placeholders');

// Error Reporting Config (Disabled in production for security)
if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}
