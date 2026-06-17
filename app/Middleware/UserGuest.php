<?php
/**
 * UserGuest.php
 * Khadomeh User Portal Guest Middleware
 */

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class UserGuest implements Middleware
{
    /**
     * Handle the incoming request.
     */
    public function handle(Request $request): ?Response
    {
        $userId = Session::get('user_id');
        
        if ($userId) {
            return Response::redirect('/user/dashboard');
        }

        return null;
    }
}
