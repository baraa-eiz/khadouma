<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Config;
use App\Core\View;

class DashboardController extends Controller
{
    /**
     * Render the admin dashboard index page wrapped in the admin layout shell.
     */
    public function index(Request $request): Response
    {
        $data = [
            'admin_name' => Session::get('admin_user_name', 'مدير النظام'),
            'app_version' => '1.0.0',
            'app_env' => Config::get('app.env', 'production'),
        ];

        // Render child view
        $content = View::render('admin/dashboard', $data);

        // Render parent layout wrapping the content
        return $this->render('layouts/admin', [
            'title' => 'لوحة التحكم',
            'content' => $content,
            'breadcrumbs' => [
                ['label' => 'الرئيسية', 'url' => '/admin/dashboard'],
                ['label' => 'لوحة التحكم']
            ]
        ]);
    }
}
