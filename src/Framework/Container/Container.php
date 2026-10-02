<?php

namespace Framework\Container;

use InvalidArgumentException,
    ReflectionClass,
    ReflectionMethod,
    ReflectionParameter,
    ReflectionNamedType,
    ArrayAccess;


class Container implements ArrayAccess {

    private array $bindings = [];
    private array $instances = [];

    public function __construct(
        private EventManager $events = new EventManager
    ){}

    // Register a shared binding (singleton)
    public function singleton(string $name, callable $fn): static {
        return $this->bind($name, $fn, true);
    }

    // Register a binding, defaults to not shared (new instance)
    public function bind(string $name, callable $fn, bool $shared = false): static {
        $this->bindings[$name] = new Binding($fn, $shared);
        return $this;
    }

    public function afterResolving(string $class_name, callable $handler): static {
        $this->events->on('afterResolving', $class_name, $handler);
        return $this;
    }

    public function get(string $class_name) {

        if (isset($this->instances[$class_name])) {

            return $this->instances[$class_name];
        }

        if ($binding = $this->bindings[$class_name] ?? null) {

            $instance = $binding->resolve($this);

        } else {
            // recursively resolve constructor dependencies
            $reflectionClass = new ReflectionClass($class_name);
            $args = [];

            if (!$reflectionClass->isInstantiable()) {
                throw new InvalidArgumentException(
                    "{$class_name} is not instantiable and has no binding."
                );
            }

            if ($params = $reflectionClass->getConstructor()?->getParameters()) {

                foreach ($params as $param) {
                    $type = $param->getType();

                    if (is_null($type) or $type->isBuiltin()) {
                        throw new InvalidArgumentException(
                            "Unable to resolve {$class_name}'s constructor '$type' parameter '{$param->getName()}' "
                        );
                    }

                    $args[] = $this->get((string) $type);
                }
            }

            $instance = new $class_name(...$args);
        }

        if (isset($binding) && $binding->isShared()) {
            // cache the shared instance
            $this->instances[$class_name] = $instance;
        }

        // this is where added listeners can perform additional configurations.
        // for instance assigning the Viewer or Response objects on a controller.
        $this->events->dispatch('afterResolving', $instance, $this);

        return $instance;
    }


    public function has(string $class_name) {
        return (
            isset($this->bindings[$class_name]) ||
            isset($this->instances[$class_name])
        );
    }

    /**
     * Gets the parameter names from the controller method and uses them
     * to pick out the required arguments from the route params array.
     *
     * @param object $object The method's instance.
     * @param string $method_name The name of the method to inspect.
     * @param array $params The parameters to match against.
     * @return array An array of arguments to pass to the controller method.
     */
    public function getMethodArguments(object $object, string $method_name, array $params): array {
        $reflection = new ReflectionMethod($object, $method_name);

        return array_map(
            fn($param) => $this->getParamValue($param, $params),
            $reflection->getParameters()
        );
    }

    /**
     * Gets the value for a given parameter from the route parameters.
     *
     * @param ReflectionParameter $param The parameter to get the value for.
     * @param array $params The parameters to match against.
     * @return mixed The value for the parameter.
     * @throws InvalidArgumentException If the parameter is required but not provided.
     */
    public function getParamValue(ReflectionParameter $param, array $params) {
        $name = $param->getName();
        $type = $param->getType();

        // have a match in the route parameters
        if (array_key_exists($name, $params)) {
            return castTo($type, $params[$name]);

        // or has a default value for the parameter
        } elseif ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();

        } elseif ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            return $this->get($type->getName()::class);

        // or is nullable
        } elseif ($param->allowsNull()) {
            return null;
        }

        throw new InvalidArgumentException("Missing required parameter '{$name}'.");
    }

    // ArrayAccess methods

    public function offsetExists(mixed $key): bool {
        return $this->has($key);
    }

    public function offsetGet(mixed $key): mixed {
        return $this->get($key);
    }

    public function offsetSet(mixed $key, mixed $value): void {
        $this->bind($key, $value);
    }

    public function offsetUnset(mixed $key): void {
        unset($this->instances[$key], $this->bindings[$key]);
    }
}