<?php
namespace Framework;

use ReflectionMethod;
use App\Controllers\ErrorController;

class Dispatcher {

    protected string $namespace = 'App\\Controllers\\';

    public function __construct(
        private Router $router,
        private Container $container
    ){}

    public function dispatch(Request $request): void {
        $method = $request->method;

        // if PUT or DELETE have been passed as a method override
        if ($method === 'POST' && isset($request->post['_method'])) {
            $method = $request->post['_method'];
        }

        // get the controller and route parameters from the router
        if (!$routeData = $this->router->match($request->uri, $method)) {
            http_response_code(404);
            $routeData = $this->router->match('/error/404');
        }

        // get the action from the controller string
        [$controller, $action] = $this->parseController($routeData['controller']);

        // get the controller class and resolve its dependencies
        $controller_class = $this->getController($controller);

        // set the viewer for the controller
        $controller_class->setViewer($this->container->resolve(ViewerInterface::class));

        // get the required controller method arguments from the route parameters
        $args = $this->getMethodArguments($controller_class, $action, $routeData['params']);

        $controller_class->$action(...$args);
    }


    /**
     * Gets the controller class from the container and sets the viewer.
     *
     * @param string $controllerClass The fully qualified class name of the controller.
     * @return object The controller instance with the viewer set.
     */
    function getController(string $controllerClass): object {
        $container = $this->container;

        return $container
            ->resolve($controllerClass)
            ->setViewer($container->resolve(ViewerInterface::class));
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
     * @param string $method The name of the method to inspect.
     * @param array $uri_params The route parameters to match against.
     * @return array An array of arguments to pass to the controller method.
     */
    protected function getMethodArguments(Controller $controller, string $method, array $uri_params): array {
        $reflection = new ReflectionMethod($controller, $method);

        return array_map(
            fn($param) => $this->getParamValue($param, $uri_params),
            $reflection->getParameters()
        );
    }

    /**
     * Gets the value for a given parameter from the route parameters.
     *
     * @param \ReflectionParameter $param The parameter to get the value for.
     * @param array $uri_params The route parameters to match against.
     * @return mixed The value for the parameter.
     * @throws \InvalidArgumentException If the parameter is required but not provided.
     */
    protected function getParamValue(\ReflectionParameter $param, array $uri_params) {
        $name = $param->getName();
        // have a match in the route parameters
        if (isset($uri_params[$name])) {
            return castTo($param->getType(), $uri_params[$name]);

        // or has a default value for the parameter
        } elseif ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();

        // or is nullable
        } elseif ($param->allowsNull()) {
            return null;
        }

        throw new \InvalidArgumentException("Missing required parameter '{$name}'.");
    }
}