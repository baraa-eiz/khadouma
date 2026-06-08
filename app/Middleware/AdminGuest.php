<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Config;

class AdminGuest implements Middleware
{
    /**
     * Handle the incoming request.
     */
    public function handle(Request $request): ?Response
    {
        $adminId = Session::get('admin_user_id');
        
        if ($adminId) {
            $lastActivity = Session::get('admin_last_activity');
            $timeout = Config::get('app.admin.session_timeout', 1800);

            if ($lastActivity && (time() - $lastActivity <= $timeout)) {
                // Active session exists, redirect to dashboard
                Session::set('admin_last_activity', time()); // Refresh activity
                return Response::redirect('/admin/dashboard');
            }
            
            // Session has expired, clear it
            Session::destroy();
        }

        return null; // Continue request lifecycle (allow displaying login form)
    }
}
