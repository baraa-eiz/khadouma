<?php

/**
 * index.php
 * Khadomeh Front Controller Entry Point
 *
 * All incoming requests are captured here and routed through the Micro-MVC framework.
 */

// Start tracking application bootstrap time
define('KHADOMEH_START', microtime(true));

// Load core bootstrapper
require_once dirname(__DIR__) . '/app/Core/Bootstrap.php';

// Boot the application
App\Core\Bootstrap::boot();

// Handle the HTTP request
$request = new App\Core\Request();
$router = new App\Core\Router();

// Load routing configurations
require_once dirname(__DIR__) . '/config/routes.php';

// Dispatch and send response
$response = $router->dispatch($request);
$response->send();
