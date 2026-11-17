<?php
namespace Framework\Providers;

abstract class MiddlewareProvider {
    protected array $global = [];
    protected array $aliases = [];

    public function globals(): array {
        return $this->global;
    }

    public function aliases(): array {
        return $this->aliases;
    }
}