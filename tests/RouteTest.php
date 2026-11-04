<?php

use Framework\Container\Container;
use Framework\Controller;
use Framework\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RouteTestController extends Controller {
    public function show(int $job_id, string $format = 'html'): void {}
}

/**
 * Route: parsing the controller string, the chained configuration
 * methods, and resolving the controller and its action arguments.
 */
class RouteTest extends TestCase {

    private function makeRoute(string $uri = '/listings', string $controller = 'TestController'): Route {
        return new Route('GET', $uri, $controller);
    }

    /** Runs a uri through the route's compiled regex. */
    private function routeMatches(Route $route, string $uri): bool {
        return preg_match($route->compilePattern(), $uri) === 1;
    }


    public static function controllerStrings(): array {
        return [
            'default action'  => ['HomeController',          'App\\Controllers\\HomeController',     'index'],
            'explicit action' => ['ListingsController@show', 'App\\Controllers\\ListingsController', 'show' ],
        ];
    }

    #[DataProvider('controllerStrings')]
    public function testParsesControllerString(string $controller, string $class, string $action): void {
        $route = $this->makeRoute('/', $controller);

        $this->assertSame($class, $route->controller_class);
        $this->assertSame($action, $route->action);
    }


    public function testConstructorKeepsMethodAndUri(): void {
        $route = new Route('POST', '/listings/store', 'TestController');

        $this->assertSame('POST', $route->method);
        $this->assertSame('/listings/store', $route->uri);
    }


    public static function chainedCalls(): array {
        return [
            'middleware'   => [fn(Route $route) => $route->middleware(['auth'])],
            'numeric'      => [fn(Route $route) => $route->numeric('id')],
            'alpha'        => [fn(Route $route) => $route->alpha('id')],
            'alphanumeric' => [fn(Route $route) => $route->alphanumeric('id')],
            'pattern'      => [fn(Route $route) => $route->pattern(['id' => '\d+'])],
            'setParams'    => [fn(Route $route) => $route->setParams(['id' => '1'])],
            'setContainer' => [fn(Route $route) => $route->setContainer(new Container)],
        ];
    }

    #[DataProvider('chainedCalls')]
    public function testConfigurationMethodsAreChainable(callable $call): void {
        $route = $this->makeRoute();

        $this->assertSame($route, $call($route));
    }


    /**
     * Each case: [helper method, request uri, expected match]
     */
    public static function patternHelpers(): array {
        return [
            'numeric: digits'             => ['numeric',      '/items/123',    true ],
            'numeric: letters'            => ['numeric',      '/items/abc',    false],
            'numeric: mixed'              => ['numeric',      '/items/12a',    false],
            'alpha: letters'              => ['alpha',        '/items/aBc',    true ],
            'alpha: digits'               => ['alpha',        '/items/123',    false],
            'alpha: mixed'                => ['alpha',        '/items/ab1',    false],
            'alphanumeric: mixed'         => ['alphanumeric', '/items/aB1',    true ],
            'alphanumeric: underscore'    => ['alphanumeric', '/items/a_1',    false],
        ];
    }

    #[DataProvider('patternHelpers')]
    public function testPatternHelpers(string $helper, string $uri, bool $expected): void {
        $route = $this->makeRoute('/items/{id}')->$helper('id');

        $this->assertSame($expected, $this->routeMatches($route, $uri));
    }


    public function testPatternSetsSeveralParams(): void {
        $route = $this->makeRoute('/users/{user}/jobs/{job}')
            ->pattern(['user' => '[a-z]+', 'job' => '\d+']);

        $this->assertTrue($this->routeMatches($route, '/users/sam/jobs/9'));
        $this->assertFalse($this->routeMatches($route, '/users/9/jobs/9'));
        $this->assertFalse($this->routeMatches($route, '/users/sam/jobs/sam'));
    }


    public function testChainedPatternsApplyToTheirOwnParams(): void {
        $route = $this->makeRoute('/users/{user}/jobs/{job}')->alpha('user')->numeric('job');

        $this->assertTrue($this->routeMatches($route, '/users/sam/jobs/9'));
        $this->assertFalse($this->routeMatches($route, '/users/9/jobs/sam'));
    }


    public function testLaterPatternReplacesEarlierOne(): void {
        $route = $this->makeRoute('/items/{id}')->numeric('id')->alpha('id');

        $this->assertTrue($this->routeMatches($route, '/items/abc'));
        $this->assertFalse($this->routeMatches($route, '/items/123'));
    }


    public function testMiddlewareKeysAccumulateInOrder(): void {
        $route = $this->makeRoute()->middleware(['auth'])->middleware(['example', 'other']);

        $this->assertSame(['auth', 'example', 'other'], $route->middleware);
    }


    public function testGetControllerResolvesFromTheContainerOnce(): void {
        $route = $this->makeRoute()->setContainer(new Container);
        $route->controller_class = RouteTestController::class;

        $controller = $route->getController();

        $this->assertInstanceOf(RouteTestController::class, $controller);
        $this->assertSame($controller, $route->getController());
    }


    public function testGetMethodArgumentsMapsParamsOntoTheAction(): void {
        $route = $this->makeRoute('/listings/{job_id}', 'TestController@show')
            ->setContainer(new Container)
            ->setParams(['job_id' => '12', 'format' => 'json']);

        $route->controller_class = RouteTestController::class;

        $this->assertSame([12, 'json'], $route->getMethodArguments());
    }


    public function testGetMethodArgumentsUsesTheActionDefaultForMissingParams(): void {
        $route = $this->makeRoute('/listings/{job_id}', 'TestController@show')
            ->setContainer(new Container)
            ->setParams(['job_id' => '12']);

        $route->controller_class = RouteTestController::class;

        $this->assertSame([12, 'html'], $route->getMethodArguments());
    }
}
