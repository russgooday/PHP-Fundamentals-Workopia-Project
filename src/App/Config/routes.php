<?php
namespace App\Config;
use Framework\Router;

class Routes {
    public static function register(Router $router): Router {

        $router
            ->get('/', 'HomeController')->middleware(['example'])
            ->get('/listings', 'ListingsController')
            ->get('/listings/create', 'ListingsController@create')
            ->get('/listings/{job_id:\d+}', 'ListingsController@show')
            ->get('/listings/{job_id:\d+}/edit', 'ListingsController@edit')
            // ->get('/error/{status_code}', 'ErrorController')

            ->post('/listings/store', 'ListingsController@store')
            ->put('/listings/{job_id:\d+}', 'ListingsController@update')
            ->delete('/listings/{job_id:\d+}', 'ListingsController@delete');

        return $router;
    }
}