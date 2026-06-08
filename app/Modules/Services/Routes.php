<?php
/**
 * Routes.php
 * Services Module Route Registry
 */

/** @var App\Core\Router $router */

$router->get('/admin/services', [App\Modules\Services\ServicesController::class, 'index'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/services/create', [App\Modules\Services\ServicesController::class, 'create'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/services', [App\Modules\Services\ServicesController::class, 'store'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/services/{id}', [App\Modules\Services\ServicesController::class, 'show'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/services/{id}/edit', [App\Modules\Services\ServicesController::class, 'edit'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/services/{id}', [App\Modules\Services\ServicesController::class, 'update'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/services/{id}/delete', [App\Modules\Services\ServicesController::class, 'delete'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/services/{id}/restore', [App\Modules\Services\ServicesController::class, 'restore'], [App\Middleware\AdminAuth::class]);
