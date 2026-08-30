<?php
namespace Framework;

/**
 * TODO: signature vs the dispatch call, else 404 branch duplicates dispatch logic
 */

class Dispatcher {

    protected string $namespace = 'App\\Controllers\\';

    public function dispatch(array $routeData, array $requestData = []): void {

        [$controllerClass, $action] = $this->resolve($routeData['controller']);

        if (!class_exists($controllerClass) or !method_exists($controllerClass, $action)) {

            http_response_code(404);

            [$controllerClass, $action] = $this->resolve('ErrorController');
            $routeData['params'] = ['status_code' => 404];
        }

        (new $controllerClass())->$action($routeData['params'] ?? [], $requestData);
    }

    protected function resolve(string $controller): array {

        if (str_contains($controller, '@')) {
            [$controller, $action] = explode('@', $controller, 2);
        }

        return [ $this->namespace . $controller, $action ?? 'index' ];
    }
}