<?php
/**
 * init.php
 * Khadomeh Application Bootstrapper
 * 
 * Sets up error handling, initializes secure session management,
 * registers the PSR-4 autoloader, and loads the helper library.
 */

// Prevent direct access to includes
define('IN_APP', true);

// Include configuration constants
require_once dirname(__DIR__) . '/config/config.php';

// Secure Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    // Session Cookie Settings
    $cookieParams = [
        'lifetime' => 86400, // 24 hours
        'path' => '/',
        'domain' => '',
        'secure' => (APP_ENV === 'production'), // Set true in production (HTTPS only)
        'httponly' => true,                     // Prevent JS access to session cookie
        'samesite' => 'Lax'                     // CSRF protection for cross-site cookie leakage
    ];
    session_set_cookie_params($cookieParams);
    session_start();
}

// PSR-4 Autoloader Implementation
spl_autoload_register(function ($class) {
    // Project-specific namespace prefix
    $prefix = 'App\\';
    // Base directory for the namespace prefix
    $baseDir = APP_DIR . '/app/';

    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, move to the next registered autoloader
        return;
    }

    // Get the relative class name
    $relativeClass = substr($class, $len);

    // Replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load Helpers
require_once APP_DIR . '/app/Helpers/security_helpers.php';
require_once APP_DIR . '/app/Helpers/url_helpers.php';
require_once APP_DIR . '/app/Helpers/text_helpers.php';
require_once APP_DIR . '/app/Helpers/seo_helpers.php';
require_once APP_DIR . '/app/Helpers/image_helpers.php';
