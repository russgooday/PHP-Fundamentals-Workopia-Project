<?php

use App\Config\Routes;
use Framework\Container\Container;
use Framework\Router;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Router: registering routes and matching them against a uri and method.
 *
 * Regex generation is covered by RouteCompilerTest,
 * and the Route object's own API by RouteTest.
 */
class RouterTest extends TestCase {

    private Router $router;

    protected function setUp(): void {
        $this->router = new Router(new Container);
    }


    /**
     * Each case: [route uri, patterns, request uri, expected params]
     * Expected params of null means the route should not match.
     */
    public static function matchCases(): array {
        return [
            // static routes
            'root'                      => ['/',                       [], '/',                    []],
            'static'                    => ['/listings/create',        [], '/listings/create',     []],
            'static: extra segment'     => ['/listings/create',        [], '/listings/create/123', null],

            // route parameters
            'param: at end'             => ['/listings/{job_id}',      [], '/listings/456',        ['job_id' => '456']],
            'param: mid path'           => ['/listings/{job_id}/edit', [], '/listings/123/edit',   ['job_id' => '123']],
            'param: wrong prefix'       => ['/listings/{job_id}',      [], '/profile/123',         null],
            'param: wrong suffix'       => ['/listings/{id}/edit',     [], '/listings/23/view',    null],
            'param: missing suffix'     => ['/listings/{id}/edit',     [], '/listings/23',         null],
            'param: missing value'      => ['/error/{status_code}',    [], '/error',               null],
            'param: two values'         => ['/users/{user}/jobs/{job}', [], '/users/5/jobs/9',     ['user' => '5', 'job' => '9']],

            // patterns
            'pattern: digits match'     => ['/listings/{job_id}', ['job_id' => '\d+'], '/listings/456',      ['job_id' => '456']],
            'pattern: digits no match'  => ['/listings/{job_id}', ['job_id' => '\d+'], '/listings/a456b',    null],
            'pattern: word match'       => ['/listings/{id}',     ['id' => '\w+'],     '/listings/abc_123',  ['id' => 'abc_123']],

            // uri normalising
            'uri: trailing slash'       => ['/listings/{job_id}', [], '/listings/456/',       ['job_id' => '456']],
            'uri: query string ignored' => ['/listings/{job_id}', [], '/listings/456?page=2', ['job_id' => '456']],
            'uri: full url'             => ['/listings/{job_id}', [], 'http://site.test/listings/456', ['job_id' => '456']],
        ];
    }

    #[DataProvider('matchCases')]
    public function testMatch(string $route_uri, array $patterns, string $request_uri, ?array $expected): void {
        $this->router->get($route_uri, 'TestController')->pattern($patterns);

        $route = $this->router->match($request_uri);

        $this->assertSame($expected, $route?->params);
    }


    public function testMatchReturnsTheRegisteredRoute(): void {
        $this->router->get('/first', 'TestController');
        $second = $this->router->get('/second', 'TestController');

        $this->assertSame($second, $this->router->match('/second'));
    }


    public function testMatchReturnsNullWithNoRoutes(): void {
        $this->assertNull($this->router->match('/listings'));
    }


    public function testFirstRegisteredRouteWins(): void {
        $create = $this->router->get('/listings/create', 'ListingsController@create');
        $this->router->get('/listings/{job_id}', 'ListingsController@show');

        $this->assertSame($create, $this->router->match('/listings/create'));
    }


    public function testRouteMatchReturnsTheNormalisedUri(): void {
        $route = $this->router->get('/listings/{job_id}', 'TestController');

        $this->assertSame(
            ['uri' => '/listings/456', 'params' => ['job_id' => '456']],
            $this->router->routeMatch($route, '/listings/456/?page=2')
        );
    }


    public static function verbs(): array {
        return [
            'get'    => ['get',    'GET'],
            'post'   => ['post',   'POST'],
            'put'    => ['put',    'PUT'],
            'delete' => ['delete', 'DELETE'],
        ];
    }

    #[DataProvider('verbs')]
    public function testVerbMethodsRegisterUnderTheirOwnMethod(string $verb, string $method): void {
        $route = $this->router->$verb('/listings', 'TestController');

        $this->assertSame($method, $route->method);
        $this->assertSame($route, $this->router->match('/listings', $method));

        foreach (array_diff(array_column(self::verbs(), 1), [$method]) as $other_method) {
            $this->assertNull($this->router->match('/listings', $other_method));
        }
    }


    public function testMatchDefaultsToGet(): void {
        $route = $this->router->get('/listings', 'TestController');
        $this->router->post('/store', 'TestController');

        $this->assertSame($route, $this->router->match('/listings'));
        $this->assertNull($this->router->match('/store'));
    }


    public function testMatchMethodIsCaseInsensitive(): void {
        $route = $this->router->post('/listings', 'TestController');

        $this->assertSame($route, $this->router->match('/listings', 'post'));
    }


    public static function methods(): array {
        return [
            'GET'             => ['GET',    true ],
            'POST'            => ['POST',   true ],
            'PUT'             => ['PUT',    true ],
            'DELETE'          => ['DELETE', true ],
            'lowercase'       => ['get',    true ],
            'unsupported'     => ['PATCH',  false],
        ];
    }

    #[DataProvider('methods')]
    public function testHasMethod(string $method, bool $expected): void {
        $this->assertSame($expected, $this->router->hasMethod($method));
    }


    /**
     * The application's real route table.
     * Each case: [method, request uri, controller, action, expected params]
     */
    public static function applicationRoutes(): array {
        return [
            'home'              => ['GET',    '/',                  'HomeController',     'index',        []],
            'listings index'    => ['GET',    '/listings',          'ListingsController', 'index',        []],
            'listings create'   => ['GET',    '/listings/create',   'ListingsController', 'create',       []],
            'listings show'     => ['GET',    '/listings/12',       'ListingsController', 'show',         ['job_id' => '12']],
            'listings edit'     => ['GET',    '/listings/12/edit',  'ListingsController', 'edit',         ['job_id' => '12']],
            'listings store'    => ['POST',   '/listings/store',    'ListingsController', 'store',        []],
            'listings update'   => ['PUT',    '/listings/12',       'ListingsController', 'update',       ['job_id' => '12']],
            'listings delete'   => ['DELETE', '/listings/12',       'ListingsController', 'delete',       ['job_id' => '12']],
            'register form'     => ['GET',    '/auth/register',     'UserController',     'create',       []],
            'login form'        => ['GET',    '/auth/login',        'UserController',     'login',        []],
            'register'          => ['POST',   '/auth/register',     'UserController',     'store',        []],
            'login'             => ['POST',   '/auth/login',        'UserController',     'authenticate', []],
            'logout'            => ['GET',    '/auth/logout',       'UserController',     'logout',       []],
        ];
    }

    #[DataProvider('applicationRoutes')]
    public function testApplicationRoutes(string $method, string $uri, string $controller, string $action, array $params): void {
        $route = Routes::register($this->router)->match($uri, $method);

        $this->assertNotNull($route, "No route matched {$method} {$uri}");
        $this->assertSame("App\\Controllers\\{$controller}", $route->controller_class);
        $this->assertSame($action, $route->action);
        $this->assertSame($params, $route->params);
    }


    public static function unmatchedApplicationRoutes(): array {
        return [
            'non-numeric show'   => ['GET',    '/listings/abc'],
            'non-numeric edit'   => ['GET',    '/listings/abc/edit'],
            'non-numeric update' => ['PUT',    '/listings/abc'],
            'non-numeric delete' => ['DELETE', '/listings/abc'],
            'wrong method'       => ['POST',   '/listings/12'],
            'unknown path'       => ['GET',    '/nowhere'],
        ];
    }

    #[DataProvider('unmatchedApplicationRoutes')]
    public function testApplicationRoutesRejectBadRequests(string $method, string $uri): void {
        $this->assertNull(Routes::register($this->router)->match($uri, $method));
    }
}
