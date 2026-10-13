<?php

namespace Framework;

class MiddlewareRequestHandler implements RequestHandlerInterface {

    public function __construct(
        private array $middlewares,
        private ControllerRequestHandler $handler
    ) {}

    public function handle(Request $request): Response {
        $middleware = array_shift($this->middlewares);

        if ($middleware === null) {
            return $this->handler->handle($request);
        }

        return $middleware->process($request, $this);
    }
}