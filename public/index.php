<?php
// require __DIR__ . '/../vendor/autoload.php';
require_once '../src/App/config/Paths.php';
require_once '../src/App/functions.php';
require_once '../autoloader.php';

use
    Framework\Container,
    Framework\Database,
    Framework\Router,
    Framework\Dispatcher,
    Framework\Request,
    App\Config\Routes;

$auto_loader = (new Autoloader())
    ->addNamespace('App\\', 'src/App/')
    ->addNamespace('Framework\\', 'src/Framework/')
    ->register();

$container = new Container;

$container->register(Database::class, fn() => new Database(...parse_ini_file('.env')));

$router = Routes::register(new Router);

$container = Dependencies::register(new Container);

$dispatcher = new Dispatcher($router, $container);

$dispatcher->dispatch(new Request);

set_error_handler(function (int $severity, string $message, string $file, int $line): void {
    logError("Error: {$message} in {$file} on line {$line}");
});
