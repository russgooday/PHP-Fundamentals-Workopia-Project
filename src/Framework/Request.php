<?php

namespace Framework;

/** Will give us a snapshot of these properties
 *  rather than the superglobals themselves, which can be modified by other code.
 */
class Request {
    // TODO: A placeholder for now. new self or new static will be introduced.
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
}