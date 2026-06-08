<?php

namespace App\Core;

class Flash
{
    private static array $messages = [];

    /**
     * Initialize flash messages by pulling them from the session.
     */
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            self::$messages = Session::get('_flash', []);
            Session::remove('_flash');
        }
    }

    /**
     * Set a flash message to be available in the next request.
     */
    public static function set(string $key, $value): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $current = Session::get('_flash', []);
            $current[$key] = $value;
            Session::set('_flash', $current);
        }
    }

    /**
     * Shortcut to set a success message.
     */
    public static function success(string $message): void
    {
        self::set('success', $message);
    }

    /**
     * Shortcut to set an error message.
     */
    public static function error(string $message): void
    {
        self::set('error', $message);
    }

    /**
     * Shortcut to set a warning message.
     */
    public static function warning(string $message): void
    {
        self::set('warning', $message);
    }

    /**
     * Shortcut to set an info message.
     */
    public static function info(string $message): void
    {
        self::set('info', $message);
    }

    /**
     * Get a flash message from the current request.
     */
    public static function get(string $key, $default = null)
    {
        return self::$messages[$key] ?? $default;
    }

    /**
     * Check if a flash message exists in the current request.
     */
    public static function has(string $key): bool
    {
        return isset(self::$messages[$key]);
    }

    /**
     * Get all flash messages for the current request.
     */
    public static function all(): array
    {
        return self::$messages;
    }
}
