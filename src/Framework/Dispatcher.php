<?php
namespace Framework;

use ReflectionClass, ReflectionMethod;

/**
 * TODO: handle 404 inside of the dispatcher?
 */

class Dispatcher {

    protected string $namespace = 'App\\Controllers\\';

    public function __construct(
        private Router $router
    ){}


    public function dispatch(Request $request): void {
        // get the controller and route parameters from the router
        if (!$routeData = $this->router->match($request->uri, $request->method)) {
            http_response_code(404);

            $routeData = [
                'controller' => 'ErrorController',
                'params' => ['status_code' => 404]
            ];
        }

        // get the action from the controller string
        [$controller, $action] = $this->parseController($routeData['controller']);

        // get the controller class and resolve its dependencies
        $controller_class = $this->resolve($controller);

        // pick the controller method arguments from the route parameters
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
     * Instantiates a class and recursively resolves its constructor dependencies.
     *
     * Classes without constructor parameters are instantiated directly. Each
     * constructor parameter is expected to have a named class type and is
     * resolved in the same way before being passed to the constructor.
     *
     * @param string $class Fully qualified class name to instantiate.
     * @return object Instantiated class with its dependencies resolved.
     */
    protected function resolve(string $class) {
        $reflectionClass = new ReflectionClass($class);
        $args = [];

        // has constructor and parameters
        if ($params = $reflectionClass?->getConstructor()?->getParameters()) {

            foreach ($params as $param) {
                $class_name = $param->getType()->getName();
                $args[] = $this->resolve($class_name);
            }
        } else {
            // no constructor
            return new $class;
        }

        return new $class(...$args);
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
        Controller $controller,
        string $methodName,
        array $params
    ): array {
        $reflection = new ReflectionMethod($controller, $methodName);

        return array_map(
            fn ($param) =>
                castTo($param->getType(), $params[$param->getName()]),
            $reflection->getParameters()
        );
    }
}