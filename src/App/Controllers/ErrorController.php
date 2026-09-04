<?php
namespace App\Controllers;

use Framework\Controller;
use Framework\Viewer;
use App\Config\HttpErrorMessages;

class ErrorController extends Controller {

    public function __construct(
        protected Viewer $viewer, // <-- this is coming out, dispatcher will set viewer
        protected HttpErrorMessages $messages
    ) {}

    public function index(int $status_code): void {
        $error_message = $this->messages->fetchError($status_code);

        if ($view = $this->viewer->render('error', $error_message)) {
            http_response_code($error_message['status_code']);
            echo $view;
        } else {
            echo "Error loading error view.";
        }
    }
}