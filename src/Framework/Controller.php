<?php
namespace Framework;
use Framework\ViewerInterface;

abstract class Controller {

    protected Request $request;
    protected Response $response;

    private static ?ViewerInterface $default_viewer;
    protected ?ViewerInterface $viewer;

    public static function setDefaultViewer(ViewerInterface $default_viewer): void {
        // Ensures that the default viewer can only be set once.
        static $viewerSet = false;

        if (!$viewerSet) {
            $viewerSet = true;

            self::$default_viewer = $default_viewer;
        } else {
            throw new \RuntimeException(
                'Default viewer has already been set.'
            );
        }
    }

    public function viewer(): ViewerInterface {
        return $this->viewer ?? self::$default_viewer;
    }

    public function setViewer(ViewerInterface $viewer): self {
        $this->viewer = $viewer;
        return $this;
    }

    public function setRequest(Request $request): self {
        $this->request = $request;
        return $this;
    }

    public function setResponse(Response $response): self {
        $this->response = $response;
        return $this;
    }

    public function view(string $template, array $data = []): Response {
        return $this->response->setBody($this->viewer()->render($template, $data));
    }

    public function redirect(string $url, string $field = 'Location'): Response {
        return $this->response->redirect($url, $field);
    }

    public function addHeader(string $name, string $value): Response {
        return $this->response->addHeader($name, $value);
    }
}