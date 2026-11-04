<?php

namespace App\Middleware;

use Framework\{Request, Response, RequestHandlerInterface};

class TrimNullify {
    public function process(Request $request, RequestHandlerInterface $next): Response {
        // Trim and nullify all string values in the request
        foreach ($request->post as $key => $value) {

            if (is_string($value)) {
                $request->post[$key] = trim($value);

                if ($request->post[$key] === '') {
                    $request->post[$key] = null;
                }
            }
        }

        return $next->handle($request);
    }
}