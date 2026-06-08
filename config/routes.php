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

// Platform Base Entry Point
$router->get('/', function() {
    return '<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منصة خدومة - البنية التحتية</title>
    <style>
        @import url(\'https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap\');
        body {
            font-family: \'Cairo\', Tahoma, sans-serif;
            background-color: #faf8f5;
            color: #4a3e3d;
            text-align: center;
            padding: 100px 20px;
            margin: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border: 1px solid #e6dfd5;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        }
        h1 {
            color: #c05c46;
            font-size: 28px;
            margin-bottom: 15px;
        }
        p {
            font-size: 16px;
            line-height: 1.6;
            color: #6e615e;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 25px;
            background-color: #c05c46;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        a:hover {
            background-color: #a64c37;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>منصة خدومة (خدومة)</h1>
        <p>تم إعداد وتشغيل إطار العمل المصغر (Micro-MVC) بنجاح. البنية التحتية الأساسية جاهزة للتطوير.</p>
        <a href="/health">زيارة صفحة التشخيص /health</a>
    </div>
</body>
</html>';
});

// Admin Panel Routes
$router->get('/admin/login', [App\Controllers\Admin\AuthController::class, 'showLogin'], [App\Middleware\AdminGuest::class]);
$router->post('/admin/login', [App\Controllers\Admin\AuthController::class, 'login'], [App\Middleware\AdminGuest::class]);
$router->post('/admin/logout', [App\Controllers\Admin\AuthController::class, 'logout'], [App\Middleware\AdminAuth::class]);
$router->get('/admin/dashboard', [App\Controllers\Admin\DashboardController::class, 'index'], [App\Middleware\AdminAuth::class]);

// Services CRUD Module
require dirname(__DIR__) . '/app/Modules/Services/Routes.php';

