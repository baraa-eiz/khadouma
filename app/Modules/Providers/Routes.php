<?php
/**
 * Routes.php
 * Providers Module Route Registry
 */

/** @var App\Core\Router $router */

$router->get('/admin/providers', [App\Modules\Providers\ProvidersController::class, 'index'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/providers/create', [App\Modules\Providers\ProvidersController::class, 'create'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/providers', [App\Modules\Providers\ProvidersController::class, 'store'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/providers/{id}', [App\Modules\Providers\ProvidersController::class, 'show'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/providers/{id}/edit', [App\Modules\Providers\ProvidersController::class, 'edit'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/providers/{id}', [App\Modules\Providers\ProvidersController::class, 'update'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/providers/{id}/delete', [App\Modules\Providers\ProvidersController::class, 'delete'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/providers/{id}/restore', [App\Modules\Providers\ProvidersController::class, 'restore'], [App\Middleware\AdminAuth::class]);
