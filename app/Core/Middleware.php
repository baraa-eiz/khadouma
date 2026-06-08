<?php

namespace App\Core;

interface Middleware
{
    /**
     * Handle the incoming HTTP request.
     *
     * @param Request $request
     * @return Response|null Return a Response to short-circuit, or null to continue.
     */
    public function handle(Request $request): ?Response;
}
