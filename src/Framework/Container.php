<?php

namespace Framework;

use ReflectionClass;
use Closure;

class Container {
    private array $registry = [];

    /**
     * Registers an anonymous function that returns a value for a given name.
     *
     * @param string $name The name to register the closure under.
     * @param Closure $fn_value The closure that returns the value.
     * @return self
     */
    public function register(string $name, Closure $fn_value): self {
        $this->registry[$name] = $fn_value;
        return $this;
    }

    /**
     * Instantiates a class and recursively resolves its constructor dependencies.
     *
     * Classes without constructor parameters are instantiated directly. Each
     * constructor parameter is expected to have a named class type and is
     * resolved in the same way before being passed to the constructor.
     *
     * @param string $class_name Fully qualified class name to instantiate.
     * @return object Instantiated class with its dependencies resolved.
     */
    public function resolve(string $class_name) {

        // do a lookup in the registry for non class values
        if (array_key_exists($class_name, $this->registry)) {
            return $this->registry[$class_name]();
        }

        $reflectionClass = new ReflectionClass($class_name);
        $args = [];

        // has constructor and parameters
        if ($params = $reflectionClass?->getConstructor()?->getParameters()) {

            foreach ($params as $param) {
                $type = $param->getType();
                $name = $param->getName();

                if (is_null($type) or $type->isBuiltin()) {
                    exit(
                        "Unable to resolve constructor parameter '{$name}'
                        of type '" . ($type ?? 'null') . "' for '{$class_name}' class"
                    );
                }

                $args[] = $this->resolve((string) $type);
            }
        } else {
            // no constructor
            return new $class_name;
        }

        return new $class_name(...$args);
    }
}