<?php

namespace Framework\Exceptions;

class HttpException extends \Exception {
    protected string $return_url;

    public function __construct(
        int $status_code,
        string $message,
        ?string $return_url = null
    ) {
        parent::__construct($message, $status_code);
        $this->return_url = $return_url ?: '/';
    }

    public function getReturnUrl(): string {
        return $this->return_url;
    }
}