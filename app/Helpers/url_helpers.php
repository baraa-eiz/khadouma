<?php

use App\Core\Config;
use App\Core\Response;

if (!function_exists('url')) {
    /**
     * Generate an absolute URL pointing to a path.
     */
    function url(string $path = ''): string
    {
        $base = rtrim(Config::get('app.url', ''), '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Generate an absolute URL pointing to a public asset.
     */
    function asset(string $path = ''): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to a specific URL path.
     */
    function redirect(string $path, int $statusCode = 302): void
    {
        Response::redirect($path, $statusCode);
    }
}

if (!function_exists('redirectBack')) {
    /**
     * Redirect back to the HTTP referrer, falling back to home if unavailable.
     */
    function redirectBack(string $fallback = '/'): void
    {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        $baseUrl = Config::get('app.url', '');

        // Prevent Open Redirect vulnerabilities
        if ($referrer && strpos($referrer, $baseUrl) === 0) {
            Response::redirect($referrer);
        } else {
            Response::redirect($fallback);
        }
    }
}
