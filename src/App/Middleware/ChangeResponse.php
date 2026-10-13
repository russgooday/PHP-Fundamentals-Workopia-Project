<?php

namespace App\Middleware;

use Framework\{Request, Response, RequestHandlerInterface};

class ChangeResponse {
    public function process(Request $request, RequestHandlerInterface $next): Response {
        $response = $next->handle($request);
        $body = $response->getBody();

        return $response->setBody($body . '<h1>Hello from the Middleware!</h1>');
    }
}