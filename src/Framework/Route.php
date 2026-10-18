<?php
namespace Framework;

use Framework\Container\Container;

class Route {
    private string $namespace = 'App\\Controllers\\';

    public ?Container $container = null;

    public ?Controller $controller = null;

    public string $controller_class;

    public string $action;

    public ?array $params;

    public ?array $middleware;


    public function __construct(
        public string $method,
        public string $uri,
        string $controller
    ) {
        [$this->controller_class, $this->action] = (
            $this->parseController($controller)
        );
    }


    /**
     * Parses the controller string from the route data into a
     * fully qualified class name and action method.
     *
     * @param string $controller The controller string from the route data.
     * @return array An array containing the class name and action method.
     */
    private function parseController(string $controller): array {

        if (str_contains($controller, '@')) {
            [$controller, $action] = explode('@', $controller, 2);
        }

        return [ $this->namespace . $controller, $action ?? 'index' ];
    }


    /**
     * Sets the middleware keys for the route.
     *
     * @param array $keys The middleware keys to set for the route.
     * @return void
     */
    public function middleware(array $keys) {
        $this->middleware ??= [];
        array_push($this->middleware, ...$keys);
    }


    /**
     * Sets the route parameters for the route.
     *
     * @param array $params The route parameters to set for the route.
     * @return $this
     */
    public function setParams(array $params) {
        $this->params = $params;
        return $this;
    }


    /**
     * Sets the container instance for the route.
     *
     * @param Container $container The container instance to set for the route.
     * @return $this
     */
    public function setContainer(Container $container) {
        $this->container = $container;
        return $this;
    }


    public function getController(): Controller {
        if (!($this->controller instanceof Controller)) {
            $this->controller = $this->container->get($this->controller_class);
        }

        return $this->controller;
    }


    /**
     * Retrieves the method arguments for the controller action associated with the route.
     *
     * @return array The method arguments for the controller action.
     */
    public function getMethodArguments(): array {
        return $this->container->getMethodArguments(
            $this->getController(), $this->action, $this->params ?? []
        );
    }
}