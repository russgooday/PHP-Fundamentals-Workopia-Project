<?php

namespace Framework\Container;

use InvalidArgumentException;
use ReflectionClass;
use ArrayAccess;

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

    public function resolve(string $class_name) {

        if (isset($this->instances[$class_name])) {

            return $this->instances[$class_name];
        }

        if ($binding = $this->bindings[$class_name] ?? null) {

            $instance = $binding->resolve($this);

        } else {
            // recursively resolve constructor dependencies

            $reflectionClass = new ReflectionClass($class_name);
            $args = [];

            if ($params = $reflectionClass->getConstructor()?->getParameters()) {

                foreach ($params as $param) {
                    $type = $param->getType();

                    if (is_null($type) or $type->isBuiltin()) {
                        throw new InvalidArgumentException(
                            "Unable to resolve {$class_name}'s constructor '$type' parameter '{$param->getName()}' "
                        );
                    }

                    $args[] = $this->resolve((string) $type);
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

    // ArrayAccess methods

    public function offsetExists(mixed $key): bool {
        return (
            isset($this->bindings[$key]) ||
            isset($this->instances[$key])
        );
    }

    public function offsetGet(mixed $key): mixed {
        return $this->resolve($key);
    }

    public function offsetSet(mixed $key, mixed $value): void {
        $this->bind($key, $value);
    }

    public function offsetUnset(mixed $key): void {
        unset($this->instances[$key], $this->bindings[$key]);
    }
}