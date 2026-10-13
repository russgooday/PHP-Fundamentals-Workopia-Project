<?php
namespace Framework;

use Framework\Container\Container;
use Framework\Exceptions\HttpException;

class Dispatcher {

    protected string $namespace = 'App\\Controllers\\';

    public function __construct(
        private Router $router,
        private Container $container,
        private array $middleware_classes = []
    ){}

    public function dispatch(Request $request): Response {
        $method = $request->method();
        $container = $this->container;

        if (!$this->router->hasMethod($method)) {
            throw new HttpException(400, "Unsupported request method '{$method}'.");
        }

        // get the controller and route parameters from the router
        if (!$routeData = $this->router->match($request->uri, $method)) {
            throw new HttpException(404);
        }

        // get the action from the controller string
        [$controller_name, $action] = $this->parseController($routeData['controller']);

        // resolve the controller
        $controller = $this->getController($controller_name);

        // create the controller request handler with the resolved controller and action
        $handler = new ControllerRequestHandler($container, $controller, $action, $routeData['params']);

        // resolve middleware
        $middleware = $this->getMiddleware($this->middleware_classes, $routeData['middleware'] ?? []);

        // create the middleware request handler with the middleware stack and the final controller handler
        $requestHandler = new MiddlewareRequestHandler($middleware, $handler);

        // execute the middleware pipeline with the request and the final handler
        return $requestHandler->handle($request);
    }


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

    /**
     * Gets the controller class from the container.
     *
     * @param string $controllerClass The fully qualified class name of the controller.
     * @return object The controller instance.
     */
    function getController(string $controllerClass): object {
        return $this->container->get($controllerClass);
    }

    /**
     * Parses the controller string from the route data into a
     * fully qualified class name and action method.
     *
     * @param string $controller The controller string from the route data.
     * @return array An array containing the class name and action method.
     */
    protected function parseController(string $controller): array {

        if (str_contains($controller, '@')) {
            [$controller, $action] = explode('@', $controller, 2);
        }

        return [ $this->namespace . $controller, $action ?? 'index' ];
    }
}