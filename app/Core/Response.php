<?php
/**
 * Response.php
 * Khadomeh Core Response Helper
 * 
 * Manages HTTP response headers, redirects, status codes, and JSON outputs.
 */

namespace App\Core;

class Response {
    /**
     * Set the HTTP response status code.
     */
    public static function setStatusCode($code) {
        http_response_code($code);
    }

    /**
     * Redirect to another URL with optional status code (default: 302).
     */
    public static function redirect($url, $code = 302) {
        self::setStatusCode($code);
        header("Location: " . $url);
        exit;
    }

    /**
     * Return a JSON response with proper headers and Unicode support.
     */
    public static function json($data, $code = 200) {
        self::setStatusCode($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
