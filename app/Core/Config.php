<?php

namespace App\Core;

class Config
{
    private static array $items = [];
    private static string $configPath = '';

    /**
     * Set the path to the configuration files.
     *
     * @param string $path
     */
    public static function setPath(string $path): void
    {
        self::$configPath = rtrim($path, '/');
    }

    /**
     * Get a configuration value using dot notation.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $parts = explode('.', $key);
        $file = $parts[0];

        // Load the file if not already loaded
        if (!isset(self::$items[$file])) {
            if (!self::load($file)) {
                return $default;
            }
        }

        $array = self::$items;
        foreach ($parts as $part) {
            if (!is_array($array) || !array_key_exists($part, $array)) {
                return $default;
            }
            $array = $array[$part];
        }

        return $array;
    }

    /**
     * Load a configuration file.
     *
     * @param string $file
     * @return bool
     */
    private static function load(string $file): bool
    {
        $filePath = self::$configPath . '/' . $file . '.php';
        if (file_exists($filePath)) {
            self::$items[$file] = require $filePath;
            return true;
        }
        return false;
    }

    /**
     * Get all configuration items.
     *
     * @return array
     */
    public static function all(): array
    {
        return self::$items;
    }
}
