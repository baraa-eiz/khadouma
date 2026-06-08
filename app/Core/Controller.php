<?php

namespace App\Core;

abstract class Controller
{
    /**
     * Render a view template.
     *
     * @param string $view Path to the view file inside views/ (e.g. 'public/home')
     * @param array $data Variables to pass to the view
     * @return Response
     */
    protected function render(string $view, array $data = []): Response
    {
        $content = View::render($view, $data);
        
        $response = new Response();
        $response->setContent($content);
        return $response;
    }

    /**
     * Create a JSON response.
     */
    protected function json(array $data, int $statusCode = 200): Response
    {
        return Response::json($data, $statusCode);
    }

    /**
     * Redirect to another URL.
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        Response::redirect($url, $statusCode);
    }

    /**
     * Validate the CSRF token from the request.
     */
    protected function validateCsrf(Request $request): void
    {
        $token = $request->input('csrf_token') ?? $request->header('X-CSRF-Token');
        if (!CSRF::validate($token)) {
            throw new \RuntimeException('Invalid or missing CSRF token.', 403);
        }
    }
}
