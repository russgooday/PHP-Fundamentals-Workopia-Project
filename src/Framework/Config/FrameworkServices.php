<?php

namespace Framework\Config;

use Framework\Container\Container;
use Framework\{Request, Session, Response};

class FrameworkServices {
    public static function register(Container $container): Container {
        $container->singleton(Request::class, fn() => Request::createFromGlobals());
        $container->singleton(Session::class, fn() => new Session);
        $container->bind(Response::class, fn() => new Response);

        return $container;
    }
}