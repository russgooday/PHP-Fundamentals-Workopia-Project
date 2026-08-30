<?php
namespace App\Config;
use Framework\Router;

class Routes {
    public static function registerRoutes(Router $router): Router {
        // May change target paths to actual controller classes.
        $router
            ->get('/', 'HomeController')
            ->get('/listings', 'ListingsController')
            ->get('/listings/create', 'ListingsController@create')
            ->get('/listings/:job_id', 'ListingsController@show')
            ->get('/error/:status_code', 'ErrorController');

        return $router;
    }
}