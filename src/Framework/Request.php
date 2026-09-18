<?php

namespace Framework;

/** Will give us a snapshot of these properties
 *  rather than the superglobals themselves, which can be modified by other code.
 */
class Request {

    public string $uri;
    public string $method;
    public array $get;
    public array $post;
    public array $files;
    public array $cookie;
    public array $server;

    public function __construct() {
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->cookie = $_COOKIE;
        $this->server = $_SERVER;
    }

    public function method(): string {
        // if PUT or DELETE have been passed as a method override
        if ($this->method === 'POST' && isset($this->post['_method'])) {
            return $this->post['_method'];
        }

        return $this->method;
    }
}