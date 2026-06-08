<?php

namespace App\Core;

class ErrorHandler
{
    /**
     * Handle PHP errors and convert them to ErrorExceptions.
     */
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting
            return false;
        }

        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * Handle uncaught exceptions.
     */
    public static function handleException(\Throwable $exception): void
    {
        // Log the error
        try {
            Logger::error($exception->getMessage(), [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);
        } catch (\Throwable $e) {
            // Fallback in case logger is down
            error_log($exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());
        }

        self::renderErrorPage($exception);
    }

    /**
     * Handle fatal errors on shutdown.
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error !== null && self::isFatal($error['type'])) {
            $exception = new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            );
            self::handleException($exception);
        }
    }

    /**
     * Check if an error type is fatal.
     */
    private static function isFatal(int $type): bool
    {
        return in_array($type, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR]);
    }

    /**
     * Render a developer-friendly error in development or a secure static page in production.
     */
    private static function renderErrorPage(\Throwable $exception): void
    {
        // Ensure headers aren't sent before setting 500 status code
        if (!headers_sent()) {
            http_response_code(500);
            if (self::isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
            } else {
                header('Content-Type: text/html; charset=utf-8');
            }
        }

        $env = Config::get('app.env', 'production');
        $isDev = ($env === 'development');

        if (self::isAjaxRequest()) {
            $response = [
                'error' => true,
                'message' => $isDev ? $exception->getMessage() : 'حدث خطأ داخلي في الخادم. يرجى المحاولة لاحقاً.'
            ];
            if ($isDev) {
                $response['file'] = $exception->getFile();
                $response['line'] = $exception->getLine();
                $response['trace'] = explode("\n", $exception->getTraceAsString());
            }
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        // Render HTML template
        if ($isDev) {
            self::renderDevHtml($exception);
        } else {
            self::renderProdHtml();
        }
        exit;
    }

    /**
     * Check if request expects JSON or is AJAX.
     */
    private static function isAjaxRequest(): bool
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }

    /**
     * Render detailed development error page.
     */
    private static function renderDevHtml(\Throwable $e): void
    {
        $message = htmlspecialchars($e->getMessage());
        $file = htmlspecialchars($e->getFile());
        $line = $e->getLine();
        $class = get_class($e);
        $trace = htmlspecialchars($e->getTraceAsString());

        echo <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>Internal Server Error (Developer Mode)</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #0e0e10; color: #ff5555; padding: 30px; line-height: 1.5; }
        .container { background: #1a1a24; border: 2px solid #ff5555; border-radius: 8px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h1 { margin-top: 0; font-size: 24px; color: #ff3333; border-bottom: 1px solid #ff5555; padding-bottom: 10px; }
        .meta { color: #f8f8f2; margin-bottom: 20px; }
        .meta strong { color: #50fa7b; }
        pre { background: #282a36; color: #f8f8f2; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 14px; border: 1px solid #44475a; }
    </style>
</head>
<body>
    <div class="container">
        <h1>[{$class}] Error Detected</h1>
        <div class="meta">
            <p><strong>Message:</strong> {$message}</p>
            <p><strong>File:</strong> {$file}</p>
            <p><strong>Line:</strong> {$line}</p>
        </div>
        <strong>Stack Trace:</strong>
        <pre>{$trace}</pre>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Render friendly public production error page.
     */
    private static function renderProdHtml(): void
    {
        echo <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خطأ في النظام - خدومة</title>
    <style>
        body { font-family: 'Cairo', Tahoma, sans-serif; background: #fdfaf6; color: #4a3e3d; text-align: center; padding: 50px 20px; }
        .card { background: white; border: 1px solid #e6dfd5; border-radius: 12px; padding: 40px; max-width: 500px; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h1 { color: #c05c46; font-size: 28px; margin-bottom: 15px; }
        p { font-size: 16px; line-height: 1.6; color: #6e615e; }
        a { display: inline-block; margin-top: 25px; padding: 10px 25px; background: #c05c46; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; transition: background 0.2s; }
        a:hover { background: #a64c37; }
    </style>
</head>
<body>
    <div class="card">
        <h1>عذراً، حدث خطأ ما!</h1>
        <p>نواجه حالياً مشكلة تقنية مؤقتة في خوادمنا. لقد تم تسجيل هذا الخطأ تلقائياً وسيقوم الفريق الفني بالعمل على إصلاحه في أقرب وقت ممكن.</p>
        <a href="/">العودة للصفحة الرئيسية</a>
    </div>
</body>
</html>
HTML;
    }
}
