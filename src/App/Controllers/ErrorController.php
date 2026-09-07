<?php
namespace App\Controllers;

use Framework\Controller;
use App\Config\HttpErrorMessages;

class ErrorController extends Controller {

    public function __construct(
        protected HttpErrorMessages $messages
    ) {}

    public function index(int $status_code, string $message = ''): void {
        $error_message = $this->messages->fetchError($status_code, $message);
        http_response_code($error_message['status_code']);

        if ($view = $this->viewer->render('error', $error_message)) {
            echo $view;
        } else {
            echo "Error loading error view.";
        }
    }
}