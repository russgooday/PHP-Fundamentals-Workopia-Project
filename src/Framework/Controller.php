<?php
namespace Framework;

use App\Config\HttpErrorMessages;

abstract class Controller {
    protected ViewerInterface $viewer;

    public function setViewer(ViewerInterface $viewer): self {
        $this->viewer = $viewer;
        return $this;
    }

    public function error(int $statusCode, string $message = ''): ?string {
        $error_message = (new HttpErrorMessages())->fetchError($statusCode, $message);
        http_response_code($error_message['status_code']);

        return $this->viewer->render('error', $error_message);
    }
}