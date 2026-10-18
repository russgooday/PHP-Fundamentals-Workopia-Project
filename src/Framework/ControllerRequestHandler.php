<?php

namespace Framework;

class ControllerRequestHandler implements RequestHandlerInterface {

    public function __construct(private Route $route){}

    public function handle(Request $request): Response {
        $route = $this->route;

        $controller = $route->getController();
        $controller->setRequest($request);

        return ($controller)->{$route->action}(...$route->getMethodArguments());
    }
}