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
        // get the controller and route parameters from the router
        if (!$routeData = $this->router->match($request->uri, $request->method)) {
            http_response_code(404);
            $routeData = $this->router->match('/error/404');
        }

        // get the action from the controller string
        [$controller, $action] = $this->parseController($routeData['controller']);

        // get the controller class and resolve its dependencies
        $controller_class = $this->getController($controller);

        // get the required controller method arguments from the route parameters
        $args = $this->getMethodArguments($controller_class, $action, $routeData['params']);

        try {
            $controller_class->$action(...$args);
        } catch (Exceptions\HttpException $e) {
            http_response_code($e->getCode());

            $errorController = $this->getController(ErrorController::class);
            $errorController->index($e->getCode(), $e->getMessage());
        }
        // $controller_class->$action(...$args); // This line is now redundant because the call is handled in the try block.
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
     * @param array $params The route parameters to match against.
     * @return array An array of arguments to pass to the controller method.
     */
    protected function getMethodArguments(Controller $controller, string $method, array $params): array {
        $reflection = new ReflectionMethod($controller, $method);

        return array_map(
            fn ($param) => castTo($param->getType(), $params[$param->getName()] ?? null),
            $reflection->getParameters()
        );
    }
}