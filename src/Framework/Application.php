<?php

namespace Framework;

use Framework\Container\Container;
use Framework\Config\{CoreServices, CoreMiddleware};
use Framework\Providers\{RoutesProvider, ServiceProvider, MiddlewareProvider};

class Application {

    protected array $middleware = ['global' => [], 'aliases' => []];

    protected array $services = [];


    private function __construct(
        protected Router $router,
        protected Container $container
    ) {}


    /**
     * Initializes the application instance with core services and middleware.
     *
     * @return static the initialized application instance.
     */
    public static function init(): static {

        static $instance = null;

        if (is_null($instance)) {

            $container = new Container();

            // register error handlers before proceeding
            static::registerErrorHandlers($container);

            $router = new Router($container);

            $instance = (new self($router, $container))
                ->addMiddleware(CoreMiddleware::class)
                ->addServices(CoreServices::class);
        }

        return $instance;
    }


    /**
     * Bootstraps the application by registering services and preparing the dispatcher.
     *
     * @return self
     */
    public function bootstrap(): self {

        $container = $this->container;

        // register all service providers with the container
        foreach ($this->services as $service) {
            $service->register($container);
        }

        $dispatcher = new Dispatcher($this->router, $container, $this->middleware);

        $container->bind(Dispatcher::class, fn() => $dispatcher);

        return $this;
    }


    /**
     * Handles an incoming HTTP request and returns the corresponding response.
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response {

        $this->container->singleton(Request::class, fn() => $request);

        return $this->container[Dispatcher::class]->dispatch($request);
    }


    public function addServices(ServiceProvider|string $services): self {

        $this->services[] = $this->resolve($services, ServiceProvider::class);

        return $this;
    }


    public function addMiddleware(MiddlewareProvider|string $middleware): self {

        $middleware = $this->resolve($middleware, MiddlewareProvider::class);

        $globals = $middleware->globals() ?? [];
        $aliases = $middleware->aliases() ?? [];

        if (!empty($globals)) {
            $this->middleware['global'] = array_merge(
                $this->middleware['global'], $globals
            );
        }

        if (!empty($aliases)) {
            $this->middleware['aliases'] = array_merge(
                $this->middleware['aliases'], $aliases
            );
        }

        return $this;
    }


    public function addRoutes(RoutesProvider|string $routes): self {

        $routes = $this->resolve($routes, RoutesProvider::class);

        $this->router = $routes->register($this->router);

        return $this;
    }


    protected static function registerErrorHandlers(Container $container): void {
        // curry the error handler for lazy evaluation
        $error_handler = curryN(2, fn($method, ...$args) =>
            $container[ErrorHandler::class]->$method(...$args)
        );

        // pass in the appropriate methods to the error handler ready for use
        set_exception_handler($error_handler('handleException'));
        set_error_handler($error_handler('handleError'));
    }


    /**
     * Resolves a class-string or object and verifies it inherits from the expected class or interface.
     *
     * @param object|string $target
     * @param string $expectedClass
     * @return object
     * @throws \InvalidArgumentException
     */
    private function resolve(object|string $target, string $expectedClass): object {
        if (is_string($target) && class_exists($target)) {
            $target = new $target();
        }

        if (!($target instanceof $expectedClass)) {
            $class_name = is_string($target) ? $target : get_class($target);

            throw new \InvalidArgumentException(
                "Invalid target: $class_name must be an instance or subclass of [{$expectedClass}]."
            );
        }

        return $target;
    }
}