<?php

use App\Core\CSRF;

if (!function_exists('e')) {
    /**
     * Escape HTML content for safe output rendering (XSS mitigation).
     */
    function e(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get the active session CSRF token.
     */
    function csrf_token(): string
    {
        return CSRF::getToken();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate HTML input tag for CSRF tokens.
     */
    function csrf_field(): string
    {
        return CSRF::field();
    }
}

if (!function_exists('verify_csrf_token')) {
    /**
     * Validate the given CSRF token.
     */
    function verify_csrf_token(?string $token): bool
    {
        return CSRF::validate($token);
    }
}
