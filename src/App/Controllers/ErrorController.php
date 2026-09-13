<?php
namespace App\Controllers;

use Framework\Controller;
use App\Config\HttpErrorMessages;

class ErrorController extends Controller {

    public function __construct(
        protected HttpErrorMessages $messages
    ) {}

    public function index(
        int $status_code,
        ?string $message = null,
        ?string $return_url = null
    ): void {
        $error_message = $this->messages->fetchError($status_code, $message, $return_url);
        http_response_code($error_message['status_code']);

        echo $this->viewer->render('error', $error_message);
    }
}