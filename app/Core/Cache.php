<?php

namespace App\Core;

class Cache
{
    private static string $cacheDir = 'cache';

    /**
     * Store data in cache.
     *
     * @param string $key Cache identifier
     * @param mixed $value Data to cache
     * @param int $ttl Time-to-live in seconds (default 3600)
     * @return bool
     */
    public static function set(string $key, $value, int $ttl = 3600): bool
    {
        try {
            $cacheFile = self::getFilePath($key);
            
            $data = [
                'expiry' => time() + $ttl,
                'value' => $value
            ];

            return Storage::put($cacheFile, serialize($data));
        } catch (\Throwable $e) {
            // Gracefully ignore caching failures (e.g. disk issues) to prevent breaking request
            Logger::warning('Cache set failed for key: ' . $key, ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Retrieve data from cache.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        try {
            $cacheFile = self::getFilePath($key);

            if (!Storage::exists($cacheFile)) {
                return $default;
            }

            $raw = Storage::get($cacheFile);
            if ($raw === null) {
                return $default;
            }

            $data = unserialize($raw);
            if (!$data || !is_array($data) || !isset($data['expiry'], $data['value'])) {
                Storage::delete($cacheFile);
                return $default;
            }

            if (time() > $data['expiry']) {
                // Cache expired
                Storage::delete($cacheFile);
                return $default;
            }

            return $data['value'];
        } catch (\Throwable $e) {
            Logger::warning('Cache get failed for key: ' . $key, ['error' => $e->getMessage()]);
            return $default;
        }
    }

    /**
     * Delete a cached value.
     */
    public static function delete(string $key): bool
    {
        try {
            $cacheFile = self::getFilePath($key);
            return Storage::delete($cacheFile);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Clear all cached files.
     */
    public static function clear(): bool
    {
        try {
            $dir = Storage::path(self::$cacheDir);
            if (!is_dir($dir)) {
                return true;
            }

            $files = glob($dir . '/*.cache');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Generate cache file name.
     */
    private static function getFilePath(string $key): string
    {
        return self::$cacheDir . '/' . md5($key) . '.cache';
    }
}
