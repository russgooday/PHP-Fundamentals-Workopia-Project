<?php

namespace Framework\Exceptions;

/**
 * Class HttpException
 * @package Framework\Exceptions
 * HTTP exception class that includes a return URL for redirection.
 * see https://www.php.net/manual/en/class.exception.php
 */
class HttpException extends \Exception {

    public function __construct(
        protected $code = 0,
        protected $message = '',
        protected $return_url = '/'
    ) {
        parent::__construct($message, $code);
    }

    public function getReturnUrl(): string {
        return $this->return_url;
    }
}