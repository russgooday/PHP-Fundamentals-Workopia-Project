<?php
require_once __DIR__ . '/../autoload.config.php';

use Framework\Application;
use App\Config\{Routes,Services, Middleware};

$app = Application::init()
    ->addRoutes(Routes::class)
    ->addServices(Services::class)
    ->addMiddleware(Middleware::class)
    ->bootstrap();
