<?php
namespace Framework\Providers;

use Framework\Container\Container;

abstract class ServiceProvider {
    abstract public function register(Container $container);
}