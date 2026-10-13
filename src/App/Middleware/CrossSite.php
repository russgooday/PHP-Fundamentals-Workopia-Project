<?php

namespace App\Middleware;

use Framework\{Request, Response, RequestHandlerInterface, Session};
use Framework\Exceptions\HttpException;

class CrossSite {
    public function __construct(private Session $session) {}

    public function process(Request $request, RequestHandlerInterface $next): Response {
        $method = $request->method();

        if ($method !== 'GET') {
            if (!hash_equals($this->session->csrfToken(), $request->post['_token'] ?? '')) {
                throw new HttpException(403);
            }
        }

        return $next->handle($request);

    }
}