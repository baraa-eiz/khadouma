<?php

namespace App\Core;

class View
{
    /**
     * Render a template file and return the output as a string.
     *
     * @param string $view Template path relative to the views directory (e.g. 'public/home')
     * @param array $data Variables to extract into the template scope
     * @return string
     */
    public static function render(string $view, array $data = []): string
    {
        $rootDir = dirname(dirname(__DIR__));
        $viewFile = $rootDir . '/views/' . ltrim($view, '/') . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View template file not found: " . $viewFile);
        }

        // Extract variables to local scope
        extract($data);

        // Capture template output
        ob_start();
        require $viewFile;
        return ob_get_clean();
    }
}
