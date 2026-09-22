<?php
require_once '../src/App/functions.php';
require_once '../autoloader.php';

$auto_loader = (new Autoloader())
    ->addNamespace('App\\', 'src/App/')
    ->addNamespace('Framework\\', 'src/Framework/')
    ->register();

use Framework\{
    Container,
    Router,
    Dispatcher,
    PHPViewer,
    Controller,
    Request,
    ErrorHandler,
    Session
};

use App\Config\{Services, Routes};

// set the default viewer for controllers
Controller::setDefaultViewer(new PHPViewer);

$request = new Request;

// register the routes
$router = Routes::register(new Router);

// register the services container
$container = Services::register(new Container, $request);

// resolve the error handler from the container
$error_handler = $container->resolve(ErrorHandler::class);

// set the global error and exception handlers
set_exception_handler([$error_handler, 'handleException']);
set_error_handler([$error_handler, 'handleError']);

Session::start();

$dispatcher = new Dispatcher($router, $container);
$response = $dispatcher->dispatch($request);

Session::storeNewMessages();

$response->send();