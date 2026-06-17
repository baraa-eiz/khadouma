<?php
/**
 * Routes.php
 * Users Module Admin Route Registry
 */

/** @var App\Core\Router $router */

$router->get('/admin/users', [App\Modules\Users\UsersController::class, 'index'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/users/create', [App\Modules\Users\UsersController::class, 'create'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/users', [App\Modules\Users\UsersController::class, 'store'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/users/{id}', [App\Modules\Users\UsersController::class, 'show'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/users/{id}/edit', [App\Modules\Users\UsersController::class, 'edit'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/users/{id}', [App\Modules\Users\UsersController::class, 'update'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/users/{id}/delete', [App\Modules\Users\UsersController::class, 'delete'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/users/{id}/restore', [App\Modules\Users\UsersController::class, 'restore'], [App\Middleware\AdminAuth::class]);
$router->post('/admin/users/{id}/status', [App\Modules\Users\UsersController::class, 'changeStatus'], [App\Middleware\AdminAuth::class]);
