<?php

namespace Framework\Validation;
use RuntimeException;

class MessageLoader {
    public function __construct(private string $file) {}

    public function load(): array {
        if (!file_exists($this->file)) {
            throw new RuntimeException("Messages file not found: {$this->file}");
        }

        return require $this->file;
    }
}