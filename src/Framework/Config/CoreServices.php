<?php

namespace Framework\Config;

use Framework\Container\Container;
use Framework\{Request, Session, Response};
use Framework\Providers\ServiceProvider;

class CoreServices extends ServiceProvider {
    public function register(Container $container): void {

        $container->singleton(Request::class, fn() => Request::createFromGlobals());

        $container->singleton(Session::class, fn() => new Session);

        $container->bind(Response::class, fn() => new Response);
    }
}