<?php

namespace App\Core;

class Storage
{
    private static string $storageRoot = '';

    /**
     * Set the storage root path.
     */
    private static function init(): void
    {
        if (self::$storageRoot === '') {
            self::$storageRoot = Config::get('app.paths.storage', dirname(dirname(__DIR__)) . '/storage');
        }
    }

    /**
     * Get the absolute path inside the storage directory.
     */
    public static function path(string $path): string
    {
        self::init();
        return self::$storageRoot . '/' . ltrim($path, '/');
    }

    /**
     * Write contents to a file inside the storage directory.
     */
    public static function put(string $path, string $content): bool
    {
        $fullPath = self::path($path);
        $directory = dirname($fullPath);

        if (!is_dir($directory)) {
            if (!@mkdir($directory, 0755, true)) {
                return false;
            }
        }

        return @file_put_contents($fullPath, $content, LOCK_EX) !== false;
    }

    /**
     * Get the contents of a file inside storage.
     */
    public static function get(string $path): ?string
    {
        $fullPath = self::path($path);
        if (file_exists($fullPath)) {
            $content = @file_get_contents($fullPath);
            return $content === false ? null : $content;
        }
        return null;
    }

    /**
     * Check if a file or directory exists.
     */
    public static function exists(string $path): bool
    {
        return file_exists(self::path($path));
    }

    /**
     * Delete a file inside storage.
     */
    public static function delete(string $path): bool
    {
        $fullPath = self::path($path);
        if (file_exists($fullPath)) {
            return @unlink($fullPath);
        }
        return false;
    }

    /**
     * Ensure a directory exists and is writable.
     */
    public static function ensureWritable(string $directory): bool
    {
        $fullPath = self::path($directory);
        if (!is_dir($fullPath)) {
            if (!@mkdir($fullPath, 0755, true)) {
                return false;
            }
        }
        return is_writable($fullPath);
    }
}
