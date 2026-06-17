<?php

namespace App\Core;

class Session
{
    /**
     * Set a session value.
     */
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session value.
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists.
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session key.
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Regenerate the session ID.
     */
    public static function regenerate(bool $deleteOldSession = true): bool
    {
        if (php_sapi_name() === 'cli' || headers_sent()) {
            return true;
        }
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Destroy the session completely.
     */
    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }
            session_destroy();
        }
    }
}
