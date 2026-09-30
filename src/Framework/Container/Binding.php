<?php

namespace Framework\Container;

use Closure;

class Binding {
    public function __construct(
        private Closure $fn, private bool $shared
    ){}

    public function isShared(): bool {
        return $this->shared;
    }

    public function resolve(Container $container): mixed {
        return ($this->fn)($container);
    }
}