<?php

namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class ProviderGuest implements Middleware
{
    /**
     * Handle the incoming request.
     */
    public function handle(Request $request): ?Response
    {
        $providerAccountId = Session::get('provider_account_id');
        
        if ($providerAccountId) {
            return Response::redirect('/provider/dashboard');
        }

        return null;
    }
}
