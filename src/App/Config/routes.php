<?php
namespace App\Config;
use Framework\Router;

class Routes {
    public static function register(Router $router): Router {

        $router->get('/', 'HomeController')->middleware(['example']);
        $router->get('/listings', 'ListingsController');
        $router->get('/listings/create', 'ListingsController@create');
        $router->get('/listings/{job_id}', 'ListingsController@show')->pattern(['job_id' => '\d+']);
        $router->get('/listings/{job_id}/edit', 'ListingsController@edit')->numeric('job_id');

        $router->post('/listings/store', 'ListingsController@store');
        $router->put('/listings/{job_id}', 'ListingsController@update')->numeric('job_id');
        $router->delete('/listings/{job_id}', 'ListingsController@delete')->numeric('job_id');

        $router->get('/auth/register', 'UserController@create');
        $router->get('/auth/login', 'UserController@login');

        $router->post('/auth/register', 'UserController@store');
        $router->post('/auth/login', 'UserController@authenticate');
        $router->get('/auth/logout', 'UserController@logout');

        return $router;
    }
}