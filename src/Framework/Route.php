<?php
namespace Framework;

use Framework\Container\Container;

class Route {
    private string $namespace = 'App\\Controllers\\';

    public ?Container $container = null;

    public ?Controller $controller = null;

    public string $controller_class;

    public string $action;

    public array $params = [];

    public array $middleware = [];

    public array $patterns = [];

    public array $defaults = [];


    public function __construct(
        public string $method,
        public string $uri,
        string $controller
    ) {
        [$this->controller_class, $this->action] = $this->parseController($controller);
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
        array_push($this->middleware, ...$keys);
        return $this;
    }


    /**
     * Sets a numeric pattern for the specified route parameter.
     *
     * @param string $param The route parameter to set the numeric pattern for.
     * @return $this
     */
    public function numeric(string $param) {
        $this->patterns[$param] = '\d+';
        return $this;
    }


    /**
     * Sets an alphabetic pattern for the specified route parameter.
     *
     * @param string $param The route parameter to set the alphabetic pattern for.
     * @return $this
     */
    public function alpha(string $param) {
        $this->patterns[$param] = '[a-zA-Z]+';
        return $this;
    }


    /**
     * Sets an alphanumeric pattern for the specified route parameter.
     *
     * @param string $param The route parameter to set the alphanumeric pattern for.
     * @return $this
     */
    public function alphanumeric(string $param) {
        $this->patterns[$param] = '[a-zA-Z0-9]+';
        return $this;
    }


    /**
     * Sets multiple patterns for the route parameters.
     *
     * @param array $patterns An associative array of route parameters and their patterns.
     * @return $this
     */
    public function pattern(array $patterns) {
        foreach ($patterns as $param => $pattern) {
            $this->patterns[$param] = $pattern;
        }
        return $this;
    }


    /**
     * Sets default values for the route parameters.
     *
     * @param array $defaults An associative array of route parameters and their default values.
     * @return $this
     */
    public function default(array $defaults) {
        foreach ($defaults as $param => $value) {
            $this->defaults[$param] = $value;
        }
        return $this;
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


    /**
     * Compile a regex route pattern for the route.
     *
     * @return string The compiled regex pattern for the route.
     */
    public function compilePattern(): string {
        $tokens = RouteCompiler::tokenize($this->uri, $this->patterns, $this->defaults);
        return RouteCompiler::compile($tokens);
    }
}