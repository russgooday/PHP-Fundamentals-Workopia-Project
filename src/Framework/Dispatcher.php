<?php
namespace Framework;

use Framework\Container\Container;
use Framework\Exceptions\HttpException;

class Dispatcher {

    public function __construct(
        private Router $router,
        private Container $container,
        private array $middleware_classes = []
    ){}

    public function dispatch(Request $request): Response {
        $method = $request->method();
        // $container = $this->container;

        if (!$this->router->hasMethod($method)) {
            throw new HttpException(400, "Unsupported request method '{$method}'.");
        }

        // get the controller and route parameters from the router
        if (!$route = $this->router->match($request->uri, $method)) {
            throw new HttpException(404);
        }

        // create the controller request handler
        $handler = new ControllerRequestHandler($route);

        // resolve middleware
        $middleware = $this->getMiddleware($this->middleware_classes, $route->middleware ?? []);

        // create the middleware request handler with the middleware stack and the final controller handler
        $requestHandler = new MiddlewareRequestHandler($middleware, $handler);

        // execute the middleware pipeline with the request and the final handler
        return $requestHandler->handle($request);
    }


    /**
     * Gets the middleware instances for the given global and route-specific middleware classes.
     *
     * @param array $global_classes The global middleware classes and their aliases.
     * @param array $route_classes The route-specific middleware classes.
     * @return array The resolved middleware instances.
     */
    function getMiddleware(array $global_classes, array $route_classes = []): array {
        ['global' => $global, 'aliases' => $aliases] = $global_classes;
        $middlewares = [];

        foreach ($global as $key => $class_name) {
            if ($middleware = $this->container->get($class_name)) {
                $middlewares[$key] = $middleware;
            }
        }

        if (empty($route_classes)) {
            return $middlewares;
        }

        foreach ($route_classes as $key) {
            if (array_key_exists($key, $aliases)) {
                $middlewares[$key] = $this->container->get($aliases[$key]);
            }
        }

        return $middlewares;
    }
}