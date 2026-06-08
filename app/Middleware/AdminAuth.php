<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Config;
use App\Core\Flash;

class AdminAuth implements Middleware
{
    /**
     * Handle the incoming request.
     */
    public function handle(Request $request): ?Response
    {
        // Check if admin is authenticated
        $adminId = Session::get('admin_user_id');
        
        if (!$adminId) {
            Flash::error('يرجى تسجيل الدخول للوصول إلى لوحة التحكم.');
            return Response::redirect('/admin/login');
        }

        // Session timeout check
        $lastActivity = Session::get('admin_last_activity');
        $timeout = Config::get('app.admin.session_timeout', 1800);

        if ($lastActivity && (time() - $lastActivity > $timeout)) {
            // Session expired
            Session::destroy();
            Flash::error('انتهت الجلسة بسبب عدم النشاط. يرجى تسجيل الدخول مرة أخرى.');
            return Response::redirect('/admin/login');
        }

        // Update last activity timestamp
        Session::set('admin_last_activity', time());

        return null; // Continue request lifecycle
    }
}
