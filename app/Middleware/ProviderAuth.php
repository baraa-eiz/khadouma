<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Config;
use App\Core\Flash;

class ProviderAuth implements Middleware
{
    /**
     * Handle the incoming request.
     */
    public function handle(Request $request): ?Response
    {
        // Check if provider is authenticated
        $providerAccountId = Session::get('provider_account_id');
        
        if (!$providerAccountId) {
            Flash::error('يرجى تسجيل الدخول للوصول إلى بوابة الحرفيين.');
            return Response::redirect('/provider/login');
        }

        // Session timeout check
        $lastActivity = Session::get('provider_last_activity');
        $timeout = Config::get('app.provider.session_timeout', 3600); // Default 1 hour

        if ($lastActivity && (time() - $lastActivity > $timeout)) {
            Session::destroy();
            Flash::error('انتهت الجلسة بسبب عدم النشاط. يرجى تسجيل الدخول مرة أخرى.');
            return Response::redirect('/provider/login');
        }

        // Update last activity timestamp
        Session::set('provider_last_activity', time());

        return null; // Continue request lifecycle
    }
}
