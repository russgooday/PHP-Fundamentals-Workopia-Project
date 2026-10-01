<?php
namespace App\Config;
use Framework\Router;

class Routes {
    public static function register(Router $router): Router {
        // May change target paths to actual controller classes.
        $router
            ->get('/', 'HomeController')
            ->get('/listings', 'ListingsController')
            ->get('/listings/create', 'ListingsController@create')
            ->get('/listings/{job_id}', 'ListingsController@show')
            ->get('/error/{status_code}', 'ErrorController')
            ->get('/listings/{job_id}/edit', 'ListingsController@edit')

            ->post('/listings/store', 'ListingsController@store')
            ->put('/listings/{job_id}', 'ListingsController@update')
            ->delete('/listings/{job_id}', 'ListingsController@delete');

        return $router;
    }
}