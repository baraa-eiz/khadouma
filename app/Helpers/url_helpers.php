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
        if (strpos($path, 'public/') === 0) {
            $path = substr($path, 7);
        }
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

if (!function_exists('base_url')) {
    /**
     * Generate an absolute URL pointing to a path.
     */
    function base_url(string $path = ''): string
    {
        return url($path);
    }
}

if (!function_exists('asset_url')) {
    /**
     * Generate an absolute URL pointing to a public asset.
     */
    function asset_url(string $path = ''): string
    {
        return asset($path);
    }
}

if (!function_exists('admin_url')) {
    /**
     * Generate an absolute URL pointing to an admin file.
     */
    function admin_url(string $path = ''): string
    {
        return url('admin/' . ltrim($path, '/'));
    }
}

if (!function_exists('is_self_link')) {
    /**
     * Check if a given URL matches the current request URL (path and sorted query parameters).
     */
    function is_self_link(string $href): bool
    {
        $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
        $currentPath = parse_url($currentUri, PHP_URL_PATH) ?: '/';
        $targetPath = parse_url($href, PHP_URL_PATH) ?: '/';

        if (trim($currentPath, '/') !== trim($targetPath, '/')) {
            return false;
        }

        $currentQueryStr = parse_url($currentUri, PHP_URL_QUERY) ?: '';
        $targetQueryStr = parse_url($href, PHP_URL_QUERY) ?: '';

        $currentQuery = [];
        parse_str($currentQueryStr, $currentQuery);
        $targetQuery = [];
        parse_str($targetQueryStr, $targetQuery);

        ksort($currentQuery);
        ksort($targetQuery);

        return $currentQuery === $targetQuery;
    }
}

