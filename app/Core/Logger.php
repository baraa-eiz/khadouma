<?php

namespace App\Core;

class Logger
{
    private static string $logFile = '';

    /**
     * Set the file path for logs.
     */
    private static function init(): void
    {
        if (self::$logFile === '') {
            $rootDir = dirname(dirname(__DIR__));
            self::$logFile = $rootDir . '/storage/logs/app.log';

            // Ensure logs directory exists
            $logDir = dirname(self::$logFile);
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
        }
    }

    /**
     * Log an INFO message.
     */
    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    /**
     * Log a WARNING message.
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    /**
     * Log an ERROR message.
     */
    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    /**
     * Write to log file.
     */
    private static function log(string $level, string $message, array $context): void
    {
        try {
            self::init();

            $timestamp = date('Y-m-d H:i:s');
            
            // Clean sensitive data from context (passwords, auth tokens)
            $context = self::cleanSensitiveData($context);
            $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';

            $logLine = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;

            // Fail-safe write: never throw exceptions from logger
            @file_put_contents(self::$logFile, $logLine, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // Silence exceptions to keep the request alive
            error_log('Logger Failure: ' . $e->getMessage());
        }
    }

    /**
     * Sanitize context from sensitive keys.
     */
    private static function cleanSensitiveData(array $data): array
    {
        $sensitiveKeys = ['password', 'password_hash', 'token', 'csrf_token', 'credit_card', 'pin'];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::cleanSensitiveData($value);
            } elseif (in_array(strtolower($key), $sensitiveKeys)) {
                $data[$key] = '********';
            }
        }

        return $data;
    }
}
