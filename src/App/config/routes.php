<?php
namespace App\Config;
use Framework\Router;

class Routes {
    public static function registerRoutes(Router $router = new Router()): Router {
        // May change target paths to actual controller classes.
        $router
            ->get('/', '/home.php')
            ->get('/listings', '/listings/index.php')
            ->get('/listings/create', '/listings/create.php')
            ->get('/listings/:job_id', '/listings/show.php')
            ->get('/error/:status_code', '/error.php');

        return $router;
    }
}