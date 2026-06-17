<?php
/**
 * UserAuth.php
 * Khadomeh User Portal Authentication Middleware
 */

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Config;
use App\Core\Flash;

class UserAuth implements Middleware
{
    /**
     * Handle the incoming request.
     */
    public function handle(Request $request): ?Response
    {
        $userId = Session::get('user_id');
        
        if (!$userId) {
            Flash::error('يرجى تسجيل الدخول للوصول إلى لوحة التحكم الخاصة بك.');
            return Response::redirect('/user/login');
        }

        // Session timeout check
        $lastActivity = Session::get('user_last_activity');
        $timeout = Config::get('app.user.session_timeout', 3600); // Default 1 hour

        if ($lastActivity && (time() - $lastActivity > $timeout)) {
            // Unset only user session keys to preserve other portal logins
            Session::remove('user_id');
            Session::remove('user_last_activity');
            Flash::error('انتهت الجلسة بسبب عدم النشاط. يرجى تسجيل الدخول مرة أخرى.');
            return Response::redirect('/user/login');
        }

        // Update last activity timestamp
        Session::set('user_last_activity', time());

        return null; // Continue request lifecycle
    }
}
