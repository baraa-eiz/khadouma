<?php

/**
 * routes.php
 * Khadomeh Routing Table
 *
 * Register GET and POST routes here.
 * Format: $router->get('/path', [Controller::class, 'method'], [Middleware::class]);
 */

/** @var App\Core\Router $router */

// Health Diagnostic Page (Only available in development mode)
$router->get('/health', [App\Controllers\HealthController::class, 'index']);

// Platform Base Entry Point (Home Page)
$router->get('/', function() {
    if (!defined('IN_APP')) {
        define('IN_APP', true);
    }
    $isLayoutCalled = true;
    $viewPath = APP_DIR . '/pages/home.php';
    ob_start();
    require APP_DIR . '/includes/layout.php';
    return ob_get_clean();
});

// Static Public Pages
$router->get('/about-us', function() {
    if (!defined('IN_APP')) {
        define('IN_APP', true);
    }
    $isLayoutCalled = true;
    $viewPath = APP_DIR . '/pages/about-us.php';
    ob_start();
    require APP_DIR . '/includes/layout.php';
    return ob_get_clean();
});

$router->get('/terms', function() {
    if (!defined('IN_APP')) {
        define('IN_APP', true);
    }
    $isLayoutCalled = true;
    $viewPath = APP_DIR . '/pages/terms.php';
    ob_start();
    require APP_DIR . '/includes/layout.php';
    return ob_get_clean();
});

$router->get('/privacy', function() {
    if (!defined('IN_APP')) {
        define('IN_APP', true);
    }
    $isLayoutCalled = true;
    $viewPath = APP_DIR . '/pages/privacy.php';
    ob_start();
    require APP_DIR . '/includes/layout.php';
    return ob_get_clean();
});

// Provider Search, Discovery & Event Tracking
$router->get('/search', [App\Controllers\ProviderController::class, 'search']);
$router->get('/provider/{slug}', [App\Controllers\ProviderController::class, 'show']);
$router->post('/api/contact', [App\Controllers\ProviderController::class, 'trackContact']);


// Admin Panel Routes
$router->get('/admin/login', [App\Controllers\Admin\AuthController::class, 'showLogin'], [App\Middleware\AdminGuest::class]);
$router->post('/admin/login', [App\Controllers\Admin\AuthController::class, 'login'], [App\Middleware\AdminGuest::class]);
$router->post('/admin/logout', [App\Controllers\Admin\AuthController::class, 'logout'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/dashboard', [App\Controllers\Admin\DashboardController::class, 'index'], [App\Middleware\AdminAuth::class]);

// Services CRUD Module
require dirname(__DIR__) . '/app/Modules/Services/Routes.php';

// Locations CRUD Module
require dirname(__DIR__) . '/app/Modules/Locations/Routes.php';

// Providers CRUD Module
require dirname(__DIR__) . '/app/Modules/Providers/Routes.php';



