<?php
namespace Framework;

abstract class Controller {
    protected Response $response;
    protected ViewerInterface $viewer;

    public function setResponse(Response $response): void {
        $this->response = $response;
    }

    public function setViewer(ViewerInterface $viewer): void {
        $this->viewer = $viewer;
    }

    public function view(string $template, array $data = []): Response {
        return $this->response->setBody($this->viewer->render($template, $data));
    }

    public function redirect(string $url, string $field = 'Location'): Response {
        return $this->response->redirect($url, $field);
    }

    public function addHeader(string $name, string $value): Response {
        return $this->response->addHeader($name, $value);
    }
}