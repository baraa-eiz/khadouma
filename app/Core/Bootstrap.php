<?php

namespace App\Core;

class Bootstrap
{
    private static bool $booted = false;

    /**
     * Boot the application core.
     *
     * @return void
     */
    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        if (!defined('IN_APP')) {
            define('IN_APP', true);
        }

        $rootDir = dirname(dirname(__DIR__));
        if (!defined('APP_DIR')) {
            define('APP_DIR', $rootDir);
        }

        // 1. Setup PSR-4 Autoloading
        self::registerAutoloader();

        // 2. Load Environment Variables
        Env::load($rootDir . '/.env');

        // 3. Setup Config Path
        Config::setPath($rootDir . '/config');

        // 4. Set Timezone
        $timezone = Config::get('app.timezone', 'Asia/Damascus');
        date_default_timezone_set($timezone);

        // 5. Setup Error and Exception Handlers
        self::registerErrorHandlers();

        // 6. Initialize Secure Session
        self::startSecureSession();

        // 7. Load Helper Files
        self::loadHelpers($rootDir . '/app/Helpers');

        self::$booted = true;
    }

    /**
     * PSR-4 Autoloader
     */
    private static function registerAutoloader(): void
    {
        spl_autoload_register(function ($class) {
            $prefix = 'App\\';
            $baseDir = dirname(__DIR__) . '/'; // points to app/

            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require_once $file;
            }
        });
    }

    /**
     * Register global error and exception handling
     */
    private static function registerErrorHandlers(): void
    {
        error_reporting(E_ALL);

        // Register Error Handler
        set_error_handler([ErrorHandler::class, 'handleError']);

        // Register Exception Handler
        set_exception_handler([ErrorHandler::class, 'handleException']);

        // Register Shutdown Handler for fatal errors
        register_shutdown_function([ErrorHandler::class, 'handleShutdown']);
    }

    /**
     * Start PHP session with secure cookie parameters
     */
    private static function startSecureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $isProd = (Config::get('app.env') === 'production');

            $cookieParams = [
                'lifetime' => 86400, // 24 hours
                'path' => '/',
                'domain' => '',
                'secure' => $isProd,
                'httponly' => true,
                'samesite' => 'Lax'
            ];

            session_set_cookie_params($cookieParams);
            session_start();
        }

        // Initialize CSRF Token if not set
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Initialize flash messages
        Flash::init();
    }

    /**
     * Load all helper functions in the Helpers directory
     */
    private static function loadHelpers(string $helpersDir): void
    {
        $helpers = [
            'security_helpers.php',
            'url_helpers.php',
            'text_helpers.php',
            'seo_helpers.php',
            'image_helpers.php'
        ];

        foreach ($helpers as $helper) {
            $filePath = $helpersDir . '/' . $helper;
            if (file_exists($filePath)) {
                require_once $filePath;
            }
        }
    }
}
