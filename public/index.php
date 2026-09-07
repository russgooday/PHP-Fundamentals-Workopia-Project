<?php
require_once '../src/App/functions.php';
require_once '../autoloader.php';

$auto_loader = (new Autoloader())
    ->addNamespace('App\\', 'src/App/')
    ->addNamespace('Framework\\', 'src/Framework/')
    ->register();

use Framework\Container,
    Framework\Router,
    Framework\Dispatcher,
    Framework\Request,
    App\Config\Services,
    App\Config\Routes;

$router = Routes::register(new Router);

$container = Services::register(new Container);

$dispatcher = new Dispatcher($router, $container);

$dispatcher->dispatch(new Request);

set_error_handler(function (int $severity, string $message, string $file, int $line): void {
    logError("Error: {$message} in {$file} on line {$line}");
});
