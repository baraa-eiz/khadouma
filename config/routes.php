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
$router->post('/api/contact', [App\Controllers\ProviderController::class, 'trackContact']);


// Admin Panel Routes
$router->get('/admin/login', [App\Controllers\Admin\AuthController::class, 'showLogin'], [App\Middleware\AdminGuest::class]);
$router->post('/admin/login', [App\Controllers\Admin\AuthController::class, 'login'], [App\Middleware\AdminGuest::class]);
$router->post('/admin/logout', [App\Controllers\Admin\AuthController::class, 'logout'], [App\Middleware\AdminAuth::class]);

// Admin Dashboard
$router->get('/admin/dashboard', [App\Controllers\Admin\DashboardController::class, 'index'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/reviews', [App\Controllers\Admin\DashboardController::class, 'reviews'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/reviews/{id}/approve', [App\Controllers\Admin\DashboardController::class, 'approveReview'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/reviews/{id}/reject', [App\Controllers\Admin\DashboardController::class, 'rejectReview'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/reviews/{id}/delete', [App\Controllers\Admin\DashboardController::class, 'deleteReview'], [App\Middleware\AdminAuth::class]);

$router->get('/admin/verification', [App\Controllers\Admin\DashboardController::class, 'verificationRequests'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/verification/{id}/approve', [App\Controllers\Admin\DashboardController::class, 'approveVerification'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/verification/{id}/reject', [App\Controllers\Admin\DashboardController::class, 'rejectVerification'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/verification/preview/{filename}', [App\Controllers\Admin\DashboardController::class, 'previewDocument'], [App\Middleware\AdminAuth::class]);

// Admin Productivity Tools
$router->get('/admin/productivity/quality', [App\Controllers\Admin\ProductivityController::class, 'qualityReport'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/productivity/merge', [App\Controllers\Admin\ProductivityController::class, 'mergeProviders'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/productivity/seo', [App\Controllers\Admin\ProductivityController::class, 'seoManager'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/productivity/seo/save', [App\Controllers\Admin\ProductivityController::class, 'saveSeo'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/productivity/seo/auto', [App\Controllers\Admin\ProductivityController::class, 'autoGenerateSeo'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/productivity/media', [App\Controllers\Admin\ProductivityController::class, 'mediaManager'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/productivity/media/clean', [App\Controllers\Admin\ProductivityController::class, 'cleanMedia'], [App\Middleware\AdminAuth::class]);

// Provider Portal Routes
$router->get('/provider/login', [App\Controllers\Provider\AuthController::class, 'showLogin'], [App\Middleware\ProviderGuest::class]);
$router->get('/provider/auth/google', [App\Controllers\Provider\AuthController::class, 'googleAuth'], [App\Middleware\ProviderGuest::class]);
$router->get('/provider/auth/google/stub', [App\Controllers\Provider\AuthController::class, 'showGoogleStub'], [App\Middleware\ProviderGuest::class]);
$router->post('/provider/auth/google/stub', [App\Controllers\Provider\AuthController::class, 'processGoogleStub'], [App\Middleware\ProviderGuest::class]);
$router->post('/provider/auth/dev', [App\Controllers\Provider\AuthController::class, 'processDevLogin'], [App\Middleware\ProviderGuest::class]);
$router->post('/provider/logout', [App\Controllers\Provider\AuthController::class, 'logout'], [App\Middleware\ProviderAuth::class]);

$router->get('/provider/dashboard', [App\Controllers\Provider\DashboardController::class, 'index'], [App\Middleware\ProviderAuth::class]);
$router->post('/provider/verify', [App\Controllers\Provider\DashboardController::class, 'uploadVerification'], [App\Middleware\ProviderAuth::class]);
$router->get('/provider/wizard', [App\Controllers\Provider\WizardController::class, 'index'], [App\Middleware\ProviderAuth::class]);
$router->post('/provider/wizard/save', [App\Controllers\Provider\WizardController::class, 'saveStep'], [App\Middleware\ProviderAuth::class]);
$router->post('/provider/wizard/submit', [App\Controllers\Provider\WizardController::class, 'submitReview'], [App\Middleware\ProviderAuth::class]);

// Wildcard route for provider public profile - must be after static /provider/ routes
$router->post('/provider/{slug}/review', [App\Controllers\ReviewController::class, 'submit']);
$router->get('/provider/{slug}', [App\Controllers\ProviderController::class, 'show']);

// Services CRUD Module
require dirname(__DIR__) . '/app/Modules/Services/Routes.php';

// Locations CRUD Module
require dirname(__DIR__) . '/app/Modules/Locations/Routes.php';

// Providers CRUD Module
require dirname(__DIR__) . '/app/Modules/Providers/Routes.php';

// FAQ Static Page
$router->get('/faq', function() {
    if (!defined('IN_APP')) {
        define('IN_APP', true);
    }
    $isLayoutCalled = true;
    $viewPath = APP_DIR . '/pages/faq.php';
    ob_start();
    require APP_DIR . '/includes/layout.php';
    return ob_get_clean();
});

// Contact Us Static Page
$router->get('/contact', function() {
    if (!defined('IN_APP')) {
        define('IN_APP', true);
    }
    $isLayoutCalled = true;
    $viewPath = APP_DIR . '/pages/contact.php';
    ob_start();
    require APP_DIR . '/includes/layout.php';
    return ob_get_clean();
});

// SEO Landing Pages (registered at the bottom to prevent route collisions)
$router->get('/services/{service}', [App\Controllers\ProviderController::class, 'serviceLanding']);
$router->get('/cities/{city}', [App\Controllers\ProviderController::class, 'cityLanding']);
$router->get('/{city}/{service}', [App\Controllers\ProviderController::class, 'cityServiceLanding']);




