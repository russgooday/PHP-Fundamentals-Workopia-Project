<?php
namespace Framework;

class Route {
    public ?array $middleware = null;

    public ?array $action = null;

    public function __construct(
        public string $method,
        public string $uri,
        public string $controller
    ) {}

    public function middleware(array $keys) {
        $this->middleware ??= [];
        array_push($this->middleware, ...$keys);
    }
}