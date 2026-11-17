<?php

namespace Framework\Config;

use Framework\Providers\MiddlewareProvider;

class CoreMiddleware extends MiddlewareProvider {
    public function globals(): array {
        return [
            \Framework\Middleware\SessionHandler::class
        ];
    }
}