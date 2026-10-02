<?php
namespace Framework;

use Framework\Container\Container;
use Framework\Exceptions\HttpException;

class Dispatcher {

    protected string $namespace = 'App\\Controllers\\';

    public function __construct(
        private Router $router,
        private Container $container
    ){}

    public function dispatch(Request $request): Response {
        // get the controller and route parameters from the router
        if (!$routeData = $this->router->match($request->uri, $request->method())) {
            throw new HttpException(404);
        }

        // get the action from the controller string
        [$controller, $action] = $this->parseController($routeData['controller']);

        // get the controller class and resolve its dependencies
        $controller_class = $this->getController($controller);

        // get the required controller method arguments from the route parameters
        $args = $this->container->getMethodArguments($controller_class, $action, $routeData['params']);
        // inspectAndDie($action);
        return $controller_class->$action(...$args);
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