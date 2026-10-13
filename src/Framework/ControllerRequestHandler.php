<?php

namespace Framework;

use Framework\Container\Container;

class ControllerRequestHandler implements RequestHandlerInterface {

    public function __construct(
        private Container $container,
        private Controller $controller,
        private string $action,
        private array $params
    ){}

    public function handle(Request $request): Response {
        $controller = $this->controller;
        $controller->setRequest($request);
        $args = $this->container->getMethodArguments($controller, $this->action, $this->params);

        return ($controller)->{$this->action}(...$args);
    }
}