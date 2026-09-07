<?php

namespace Framework\Exceptions;

class HttpException extends \Exception {

    protected string $return_url = '/';

    public function __construct(
        int $status_code,
        string $message,
        string $return_url = '/'
    ) {
        parent::__construct($message, $status_code);
        inspect($this->getTrace());
    }

    // public function setReturnUrl(string $url): void {
    //     $this->return_url = $url;
    // }

    public function getReturnUrl(): string {
        return $this->return_url;
    }
}