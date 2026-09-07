<?php
namespace Spikes;

use Closure, ReflectionClass, InvalidArgumentException;

class Container {
    private array $registry = [];
    private array $cache = [];

    /**
     * Registers an anonymous function that returns a value for a given name.
     *
     * @param string $name The name to register the closure under.
     * @param Closure $fn The closure that returns the value.
     * @return static
     */
    public function register(string $name, Closure $fn): static {
        $this->registry[$name] = $fn;
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

        if (isset($this->cache[$class_name])) {
            return $this->cache[$class_name];
        }

        if (isset($this->registry[$class_name])) {

            $instance = $this->registry[$class_name]($this);
        } else {

            $reflectionClass = new ReflectionClass($class_name);
            $args = [];

            // has constructor and parameters
            if ($params = $reflectionClass->getConstructor()?->getParameters()) {

                foreach ($params as $param) {
                    $type = $param->getType();

                    if (is_null($type) or $type->isBuiltin()) {

                        throw new InvalidArgumentException(
                            "Unable to resolve {$class_name}'s " .
                            "constructor '$type' parameter '{$param->getName()}' "
                        );
                    }

                    $args[] = $this->resolve((string) $type);
                }
            }

            $instance = new $class_name(...$args);
        }

        $this->cache[$class_name] = $instance;
        return $instance;
    }
}