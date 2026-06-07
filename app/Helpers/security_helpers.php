<?php
/**
 * security_helpers.php
 * Khadomeh Security Helpers
 * 
 * Provides features for CSRF protection and XSS mitigation.
 */

/**
 * Escape HTML content for safe output rendering to prevent Cross-Site Scripting (XSS).
 * Shorthand alias of htmlspecialchars().
 */
if (!function_exists('e')) {
    function e($value) {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

/**
 * Generate a cryptographically secure CSRF token and save it to the session.
 */
if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

/**
 * Verify if the submitted token matches the token stored in the session.
 */
if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

/**
 * Generate a hidden CSRF token input field for HTML forms.
 */
if (!function_exists('csrf_field')) {
    function csrf_field() {
        return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
    }
}
