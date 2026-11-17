<?php

namespace Framework\Providers;

use Framework\Router;

abstract class RoutesProvider {
    abstract public function register(Router $router): Router;
}