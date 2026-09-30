<?php
namespace Framework\Container;

class EventManager {
    /**
     * Manages event listeners for various event types.
     */

    private array $listeners = [];

    /**
     * Registers a handler to be called whenever an event of the given type is dispatched.
     *
     * @param string $type The event type to listen for.
     * @param callable $handler The callback to invoke when the event is dispatched.
     * @return callable The same handler, so it can be kept for later removal.
     */
    public function add(string $type, callable $handler): callable {
        if(!isset($this->listeners[$type])) {
            $this->listeners[$type] = [];
        }

        $this->listeners[$type][] = $handler;
        return $handler;
    }

    /**
     * Removes a previously registered handler for the given event type.
     *
     * Matches by identity, so a handler can only be removed using the exact
     * callable reference returned by add() or on() — a separately created
     * closure with equivalent behaviour will not match.
     *
     * @param string $type The event type the handler was registered under.
     * @param callable $handler The handler to remove.
     */
    public function remove(string $type, callable $handler): void {
        if ($listeners = $this->listeners[$type] ?? null) {
            $this->listeners[$type] = array_filter(
                $listeners,
                function ($stored_handler) use ($handler) {
                    return ($stored_handler !== $handler);
                }
            );

            if (empty($this->listeners[$type])) {
                unset($this->listeners[$type]);
            }
        }
    }

    /**
     * Calls every handler registered for the given event type, in registration order.
     *
     * @param string $type The event type to dispatch.
     * @param mixed ...$args Arguments passed through to each handler.
     */
    public function dispatch(string $type, mixed ...$args): void {
        if($listeners = $this->listeners[$type] ?? null) {
            array_walk($listeners, fn($handler) => $handler(...$args));
        }
    }

    /**
     * Registers a handler that only fires when the event's first argument
     * is an instance of the given class or interface.
     *
     * Wraps $handler in a type-checking closure before registering it, so
     * callers don't need to guard their own handler body with instanceof.
     *
     * @param string $type The event type to listen for.
     * @param string $class_name The class or interface the first argument must be an instance of.
     * @param callable $handler The callback to invoke when the type check passes.
     * @return callable The generated wrapper closure
     */
    public function on(string $type, string $class_name, callable $handler): callable {
        return $this->add(
            $type,
            function($instance, ...$args) use ($class_name, $handler) {
                if ($instance instanceof $class_name) {
                    $handler($instance, ...$args);
                }
            }
        );
    }
}