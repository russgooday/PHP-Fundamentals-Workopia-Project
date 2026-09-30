<?php
require_once '../src/App/functions.php';
require_once '../autoloader.php';

$auto_loader = (new Autoloader())
    ->addNamespace('App\\', 'src/App/')
    ->addNamespace('Framework\\', 'src/Framework/')
    ->register();

use Framework\{
    Router,
    Dispatcher,
    Request,
    ErrorHandler,
    Session
};

use Framework\Container\Container;

use App\Config\{Services, Routes};

// register the services container
$container = Services::register(new Container);

// curry the error handler for lazy evaluation
$error_handler = curryN(2, fn($method, ...$args) =>
    $container[ErrorHandler::class]->$method(...$args)
);

// pass in the appropriate methods to the error handler ready for use
set_exception_handler($error_handler('handleException'));
set_error_handler($error_handler('handleError'));

// register the routes
$router = Routes::register(new Router);

$session = $container->resolve(Session::class);
$session->start();

$dispatcher = new Dispatcher($router, $container);
$response = $dispatcher->dispatch(new Request);

$session->storeNewMessages();

$response->send();