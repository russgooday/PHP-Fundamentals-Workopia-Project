<?php
require_once '../src/App/config/Paths.php';
require_once '../src/App/functions.php';
require_once '../autoloader.php';

set_error_handler(function (int $severity, string $message, string $file, int $line): void {
    logError("Error: {$message} in {$file} on line {$line}");
});

use App\Config\Routes;

$router = Routes::registerRoutes();
$router->route($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);