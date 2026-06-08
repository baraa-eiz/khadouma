<?php

namespace App\Core;

class CSRF
{
    /**
     * Get the current CSRF token from session.
     */
    public static function getToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            return '';
        }
        return Session::get('csrf_token', '');
    }

    /**
     * Generate a raw HTML hidden field containing the CSRF token.
     */
    public static function field(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Validate a submitted token against the session token.
     */
    public static function validate(?string $token): bool
    {
        $sessionToken = self::getToken();
        if (empty($sessionToken) || empty($token)) {
            return false;
        }
        return hash_equals($sessionToken, $token);
    }
}
