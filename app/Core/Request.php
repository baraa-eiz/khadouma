<?php

namespace App\Core;

class Request
{
    private array $query;
    private array $body;
    private array $server;
    private array $cookies;
    private array $files;
    private ?array $json = null;

    public function __construct()
    {
        $this->query = $_GET;
        $this->body = $_POST;
        $this->server = $_SERVER;
        $this->cookies = $_COOKIE;
        $this->files = $_FILES;

        // Parse JSON body if applicable
        $contentType = $this->server['CONTENT_TYPE'] ?? $this->server['HTTP_CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $this->json = json_decode($raw, true) ?? [];
        }
    }

    /**
     * Get request URI path without query string.
     */
    public function getPath(): string
    {
        $path = $this->server['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }

        // Adjust path if running in a subdirectory under XAMPP
        $appUrlPath = parse_url(Config::get('app.url', ''), PHP_URL_PATH);
        if ($appUrlPath && $appUrlPath !== '/' && strpos($path, $appUrlPath) === 0) {
            $path = substr($path, strlen($appUrlPath));
        }

        // Normalize empty and slashes
        if (empty($path)) {
            $path = '/';
        }

        if ($path !== '/' && substr($path, -1) === '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    /**
     * Get request HTTP method (GET, POST, etc.)
     */
    public function getMethod(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Check if request method is GET.
     */
    public function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }

    /**
     * Check if request method is POST.
     */
    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    /**
     * Get input data from GET parameters.
     */
    public function query(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    /**
     * Get input data from POST parameters (or JSON if applicable).
     */
    public function input(string $key = null, $default = null)
    {
        if ($this->json !== null) {
            if ($key === null) {
                return $this->json;
            }
            return $this->json[$key] ?? $default;
        }

        if ($key === null) {
            return $this->body;
        }
        return $this->body[$key] ?? $default;
    }

    /**
     * Get uploaded files.
     */
    public function files(string $key = null)
    {
        if ($key === null) {
            return $this->files;
        }
        return $this->files[$key] ?? null;
    }

    /**
     * Get cookie value.
     */
    public function cookie(string $key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Get client IP Address.
     */
    public function getIp(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR'] 
            ?? $this->server['HTTP_CLIENT_IP'] 
            ?? $this->server['REMOTE_ADDR'] 
            ?? '127.0.0.1';
    }

    /**
     * Get a specific header.
     */
    public function header(string $key): ?string
    {
        $normalizedKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$normalizedKey] ?? $this->server[$key] ?? null;
    }
}
