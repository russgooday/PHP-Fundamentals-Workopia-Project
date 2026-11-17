<?php
namespace Framework\Middleware;

use Framework\{Session, Request, Response, RequestHandlerInterface};

class SessionHandler {
    public function __construct(private Session $session) {}

    public function process(Request $request, RequestHandlerInterface $next): Response {
        $this->session->start();

        // allows for cleanup of session messages even
        // if an exception occurs
        try {
            $response = $next->handle($request);
        } finally {
            $this->session->storeNewMessages();
        }

        return $response;
    }
}