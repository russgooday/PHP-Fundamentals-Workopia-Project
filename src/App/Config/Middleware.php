<?php

namespace App\Config;

use Framework\Providers\MiddlewareProvider;

class Middleware extends MiddlewareProvider {

    protected array $global = [
        \App\Middleware\CrossSite::class,
        \App\Middleware\TrimNullify::class
    ];

    protected array $aliases = [
        // 'auth' => \App\Middleware\Authenticate::class
    ];
}