<?php
// require __DIR__ . '/../vendor/autoload.php';
require_once '../src/App/config/Paths.php';
require_once '../src/App/functions.php';
require_once '../autoloader.php';

use Framework\Router;
use Framework\Dispatcher;
use Framework\Request;
use App\Config\Routes;

$auto_loader = (new Autoloader())
    ->addNamespace('App\\', 'src/App/')
    ->addNamespace('Framework\\', 'src/Framework/')
    ->register();

$router = Routes::registerRoutes(new Router);
$dispatcher = new Dispatcher($router);
$dispatcher->dispatch(new Request);

set_error_handler(function (int $severity, string $message, string $file, int $line): void {
    logError("Error: {$message} in {$file} on line {$line}");
});
