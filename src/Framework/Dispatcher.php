<?php
namespace Framework;

use ReflectionMethod;

class Dispatcher {

    protected string $namespace = 'App\\Controllers\\';

    public function __construct(
        private Router $router,
        private Container $container
    ){}


    public function dispatch(Request $request): void {
        // get the controller and route parameters from the router
        if (!$routeData = $this->router->match($request->uri, $request->method)) {
            http_response_code(404);
            $routeData = $this->router->match('/error/404');
        }

        // get the action from the controller string
        [$controller, $action] = $this->parseController($routeData['controller']);

        // get the controller class and resolve its dependencies
        $controller_class = $this->container->resolve($controller);

        // get the required controller method arguments from the route parameters
        $args = $this->getMethodArguments($controller_class, $action, $routeData['params']);

        $controller_class->$action(...$args);
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


    /**
     * Gets the parameter names from the controller method and uses them
     * to pick out the required arguments from the route params array.
     *
     * @param Controller $controller The controller instance.
     * @param string $methodName The name of the method to inspect.
     * @param array $params The route parameters to match against.
     * @return array An array of arguments to pass to the controller method.
     */
    protected function getMethodArguments(
        Controller $controller, string $methodName, array $params
    ): array {
        $reflection = new ReflectionMethod($controller, $methodName);

        return array_map(
            fn ($param) => castTo($param->getType(), $params[$param->getName()]),
            $reflection->getParameters()
        );
    }
}