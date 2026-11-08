<?php

namespace Framework\Container;

use InvalidArgumentException,
    ReflectionClass,
    ReflectionMethod,
    ReflectionParameter,
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

    // Manually set an instance for a given name (useful for pre-existing objects)
    public function setInstance(string $name, object $instance): static {
        $this->instances[$name] = $instance;
        return $this;
    }

    // Register a callback to be executed after a specific service is resolved.
    public function afterResolving(string $name, callable $handler): static {
        $this->events->on('afterResolving', $name, $handler);
        return $this;
    }

    /**
     * Resolve an instance by class or interface name.
     * Returns a cached shared instance if one exists, otherwise uses the registered
     * binding, or autowires the class by recursively resolving its constructor
     * dependencies. Afterwards, any afterResolving handlers are dispatched.
     *
     * @param string $name The class or interface name to resolve.
     * @return object The resolved instance.
     * @throws InvalidArgumentException If the name has no binding and is not instantiable,
     * or a constructor parameter has no resolvable type.
     */
    public function get(string $name): object {

        if (isset($this->instances[$name])) {

            return $this->instances[$name];
        }

        if ($binding = $this->bindings[$name] ?? null) {

            $instance = $binding->resolve($this);

        } else {
            // recursively resolve constructor dependencies
            $reflectionClass = new ReflectionClass($name);
            $args = [];

            if (!$reflectionClass->isInstantiable()) {
                throw new InvalidArgumentException("{$name} is not instantiable and has no binding.");
            }

            if ($params = $reflectionClass->getConstructor()?->getParameters()) {
                foreach ($params as $param) {
                    $args[] = $this->getParamValue($param);
                }
            }

            $instance = new $name(...$args);
        }

        if (isset($binding) && $binding->isShared()) {
            // cache the shared instance
            $this->setInstance($name, $instance);
        }

        // this is where added listeners can perform additional configurations.
        // for instance assigning the Viewer or Response objects on a controller.
        $this->events->dispatch('afterResolving', $instance, $this);

        return $instance;
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
        return array_map(
            fn($param) => $this->getParamValue($param, $params),
            (new ReflectionMethod($object, $method_name))->getParameters()
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
    public function getParamValue(ReflectionParameter $param, array $params = []): mixed {
        $name = $param->getName();
        $type = $param->getType();

        if ($params && array_key_exists($name, $params)) {
            return castTo($type, $params[$name]);
        }

        // 2. Class dependency (autowired from container)
        if (isSingleType($type) && !$type->isBuiltin()) {
            try {
                return $this->get($type->getName());
            } catch (\InvalidArgumentException $e) {
                // If the container couldn't resolve it, fall through to default if available!
                if (!$param->isDefaultValueAvailable() && !$param->allowsNull()) {
                    throw $e;
                }
            }
        }

        // or has a default value for the parameter
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }
        // or is nullable
        if ($param->allowsNull()) {
            return null;
        }

        throw new InvalidArgumentException("Unable to resolve parameter '{$name}'.");
    }

    // ArrayAccess methods

    public function has(string $name) {
        return (
            isset($this->bindings[$name]) ||
            isset($this->instances[$name])
        );
    }

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