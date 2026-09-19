<?php
namespace Framework;

abstract class Controller {

    protected Request $request;
    protected ViewerInterface $viewer;

    public function setViewer(ViewerInterface $viewer): self {
        $this->viewer = $viewer;
        return $this;
    }

    public function setRequest(Request $request): self {
        $this->request = $request;
        return $this;
    }

    public function view(string $template, array $data = []): ?string {
        return $this->viewer->render($template, $data);
    }

    public function redirect(string $url, string $field = 'Location'): void {
        header("{$field}: {$url}");
        exit;
    }
}