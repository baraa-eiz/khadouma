<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    /**
     * Register a GET route.
     */
    public function get(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    /**
     * Register a POST route.
     */
    public function post(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    /**
     * Register a generic route.
     */
    private function addRoute(string $method, string $path, $handler, array $middlewares): void
    {
        $path = '/' . trim($path, '/');
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    /**
     * Resolve and dispatch the incoming request.
     */
    public function dispatch(Request $request): Response
    {
        $requestMethod = $request->getMethod();
        $requestPath = '/' . trim($request->getPath(), '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            // Convert route wildcards (e.g. {city}) to capture groups
            $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $requestPath, $matches)) {
                array_shift($matches); // Remove full match

                // 1. Execute route-specific middlewares
                foreach ($route['middlewares'] as $middlewareClass) {
                    if (class_exists($middlewareClass)) {
                        $middleware = new $middlewareClass();
                        $result = $middleware->handle($request);
                        if ($result instanceof Response) {
                            return $result; // Short-circuit response
                        }
                    }
                }

                // 2. Execute target handler
                $handler = $route['handler'];
                
                if (is_array($handler) && count($handler) === 2) {
                    [$controllerClass, $method] = $handler;
                    if (class_exists($controllerClass)) {
                        $controller = new $controllerClass();
                        
                        // Pass Request object and any URL parameters to the action
                        $params = array_merge([$request], $matches);
                        $result = call_user_func_array([$controller, $method], $params);
                        
                        if ($result instanceof Response) {
                            return $result;
                        }
                        
                        $response = new Response();
                        $response->setContent((string)$result);
                        return $response;
                    }
                } elseif (is_callable($handler)) {
                    $params = array_merge([$request], $matches);
                    $result = call_user_func_array($handler, $params);
                    
                    if ($result instanceof Response) {
                        return $result;
                    }
                    
                    $response = new Response();
                    $response->setContent((string)$result);
                    return $response;
                }
            }
        }

        // Return a clean 404 Response
        return self::renderNotFound();
    }

    /**
     * Render the default 404 Not Found Page.
     */
    private static function renderNotFound(): Response
    {
        $response = new Response();
        $response->setStatusCode(404);
        
        $viewsDir = Config::get('app.paths.root') . '/views';
        $notFoundFile = $viewsDir . '/404.php';
        
        if (file_exists($notFoundFile)) {
            ob_start();
            require $notFoundFile;
            $response->setContent(ob_get_clean());
        } else {
            $response->setContent('<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>404 - الصفحة غير موجودة</title>
    <style>
        body { font-family: Tahoma, sans-serif; text-align: center; padding: 50px; background: #faf8f5; color: #555; }
        h1 { color: #c05c46; }
    </style>
</head>
<body>
    <h1>404 - الصفحة غير موجودة</h1>
    <p>عذراً، الرابط الذي طلبته غير موجود أو تم نقله.</p>
    <a href="/">العودة للرئيسية</a>
</body>
</html>');
        }
        
        return $response;
    }
}
