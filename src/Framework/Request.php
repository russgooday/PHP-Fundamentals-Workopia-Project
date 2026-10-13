<?php

namespace Framework;

/** Will give us a snapshot of these properties
 *  rather than the superglobals themselves, which can be modified by other code.
 */
class Request {
    public function __construct(
        public string $uri,
        public string $method,
        public array $get,
        public array $post,
        public array $files,
        public array $cookie,
        public array $server
    ) {}

    public static function createFromGlobals(): self {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new static(
                $_SERVER['REQUEST_URI'],
                $_SERVER['REQUEST_METHOD'],
                $_GET,
                $_POST,
                $_FILES,
                $_COOKIE,
                $_SERVER
            );
        }

        return $instance;
    }

    public function method(): string {
        $_method = $this->post['_method'] ?? null;

        // if PUT or DELETE have been passed as a method override
        if ($this->method === 'POST' && is_string($_method)) {
            return $_method;
        }

        return $this->method;
    }
}