<?php
session_start();

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
    Framework\ErrorHandler,
    App\Config\Services,
    App\Config\Routes;

$request = new Request;

$router = Routes::register(new Router);

$container = Services::register(new Container, $request);

$error_handler = new ErrorHandler($container);

set_exception_handler([$error_handler, 'handleException']);
set_error_handler([$error_handler, 'handleError']);

$dispatcher = new Dispatcher($router, $container);

$dispatcher->dispatch(new Request);

set_error_handler(function (int $severity, string $message, string $file, int $line): void {
    logError("Error: {$message} in {$file} on line {$line}");
});
