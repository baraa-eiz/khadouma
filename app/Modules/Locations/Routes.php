<?php
/**
 * Routes.php
 * Locations Module Route Registry
 */

/** @var App\Core\Router $router */

// Cities CRUD Routes
$router->get('/admin/cities', [App\Modules\Locations\CitiesController::class, 'index'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/cities/create', [App\Modules\Locations\CitiesController::class, 'create'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/cities', [App\Modules\Locations\CitiesController::class, 'store'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/cities/{id}', [App\Modules\Locations\CitiesController::class, 'show'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/cities/{id}/edit', [App\Modules\Locations\CitiesController::class, 'edit'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/cities/{id}/update', [App\Modules\Locations\CitiesController::class, 'update'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/cities/{id}/delete', [App\Modules\Locations\CitiesController::class, 'delete'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/cities/{id}/restore', [App\Modules\Locations\CitiesController::class, 'restore'], [App\Middleware\AdminAuth::class]);

// Areas CRUD Routes
$router->get('/admin/areas', [App\Modules\Locations\AreasController::class, 'index'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/areas/create', [App\Modules\Locations\AreasController::class, 'create'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/areas', [App\Modules\Locations\AreasController::class, 'store'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/areas/{id}', [App\Modules\Locations\AreasController::class, 'show'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/areas/{id}/edit', [App\Modules\Locations\AreasController::class, 'edit'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/areas/{id}/update', [App\Modules\Locations\AreasController::class, 'update'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/areas/{id}/delete', [App\Modules\Locations\AreasController::class, 'delete'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/areas/{id}/restore', [App\Modules\Locations\AreasController::class, 'restore'], [App\Middleware\AdminAuth::class]);
