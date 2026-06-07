<?php
/**
 * Router.php
 * Khadomeh Core Routing System
 * 
 * A lightweight front-controller routing class.
 * It resolves request URIs, handles XAMPP subfolders dynamically,
 * and routes requests to appropriate pages.
 */

namespace App\Core;

class Router {
    private $routes = [];

    /**
     * Register a custom route and callback.
     */
    public function add($route, $callback) {
        $this->routes[$route] = $callback;
    }

    /**
     * Dispatch the request.
     */
    public function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove subfolder prefix (e.g., /khadomeh) if running locally
        $basePath = parse_url(APP_URL, PHP_URL_PATH);
        if ($basePath && $basePath !== '/' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        // Normalize empty URI or missing leading slash
        if (empty($uri)) {
            $uri = '/';
        }
        
        // Trim trailing slash for routing parity (except root)
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }

        // 1. Check registered custom routes
        if (array_key_exists($uri, $this->routes)) {
            $callback = $this->routes[$uri];
            if (is_callable($callback)) {
                return call_user_func($callback);
            }
        }

        // 2. Default route for Home
        if ($uri === '/' || $uri === '/index.php') {
            require APP_DIR . '/pages/home.php';
            return;
        }

        // 3. Fallback to /pages/[slug].php for clean page rendering
        $pageSlug = trim($uri, '/');
        $pageFile = APP_DIR . '/pages/' . $pageSlug . '.php';
        
        if (!empty($pageSlug) && file_exists($pageFile) && strpos($pageSlug, '..') === false) {
            require $pageFile;
            return;
        }

        // 4. Default fallback: Route Not Found (404)
        Response::setStatusCode(404);
        require APP_DIR . '/pages/not-found.php';
    }
}
