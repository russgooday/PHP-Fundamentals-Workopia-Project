<?php
namespace App\Config;
use Framework\Router;

class Routes {
    public static function register(Router $router): Router {

        $router->get('/', 'HomeController')->middleware(['example']);
        $router->get('/listings', 'ListingsController');
        $router->get('/listings/create', 'ListingsController@create');
        $router->get('/listings/{job_id:\d+}', 'ListingsController@show');
        $router->get('/listings/{job_id:\d+}/edit', 'ListingsController@edit');
        // $router->get('/error/{status_code}', 'ErrorController');

        $router->post('/listings/store', 'ListingsController@store');
        $router->put('/listings/{job_id:\d+}', 'ListingsController@update');
        $router->delete('/listings/{job_id:\d+}', 'ListingsController@delete');

        return $router;
    }
}