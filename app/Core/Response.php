<?php

namespace App\Core;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private string $content = '';

    /**
     * Set the HTTP status code.
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Set a header value.
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set response HTML or text content.
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Send headers and body output.
     */
    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->statusCode);

            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }
        }

        echo $this->content;
    }

    /**
     * Create a JSON response.
     */
    public static function json(array $data, int $statusCode = 200): self
    {
        $response = new self();
        $response->setStatusCode($statusCode);
        $response->setHeader('Content-Type', 'application/json; charset=utf-8');
        $response->setContent(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response;
    }

    /**
     * Create a redirect response.
     */
    public static function redirect(string $url, int $statusCode = 302): void
    {
        // If it's a relative path, prepends application URL base
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            $base = rtrim(Config::get('app.url', ''), '/');
            $url = $base . '/' . ltrim($url, '/');
        }

        if (!headers_sent()) {
            http_response_code($statusCode);
            header("Location: {$url}");
            exit;
        }

        // JS fallback in case headers were sent
        echo "<script>window.location.href='{$url}';</script>";
        exit;
    }
}
