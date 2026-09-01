<?php
namespace App\Controllers;

use Framework\Controller;
use Framework\Viewer;
use App\Config\HttpErrorMessages;

class ErrorController extends Controller {

    public function __construct(
        protected Viewer $viewer,
        protected HttpErrorMessages $messages
    ) {}

    public function index(int $status_code): void {
        if ($output = $this->viewer->render(
            'error', $this->messages->fetchError($status_code ?? 500)
        )) {
            echo $output;
        } else {
            echo "Error loading error view.";
        }
    }
}