<?php

namespace Framework\Exceptions;

class ViewNotFoundException extends HttpException {
    public function __construct(string $message) {
        parent::__construct(404, $message);
    }
}