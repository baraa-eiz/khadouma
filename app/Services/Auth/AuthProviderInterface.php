<?php

namespace App\Services\Auth;

use App\Core\Request;

interface AuthProviderInterface
{
    /**
     * Authenticate a provider request and return their account details.
     *
     * @param Request $request
     * @return array|null
     * @throws \Exception
     */
    public function authenticate(Request $request): ?array;
}
