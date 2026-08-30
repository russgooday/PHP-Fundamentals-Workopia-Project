<?php
namespace App\Controllers;

use Framework\Controller;
use Framework\Template;
use App\Config\HttpErrorMessages;

class ErrorController extends Controller {

    public function __construct(
        protected Template $template = new Template(),
        protected HttpErrorMessages $messages = new HttpErrorMessages()
    ) {}

    public function index(array $params): void {
        if ($output = $this->template->render(
            'error', $this->messages->fetchError($params['status_code'] ?? 500)
        )) {
            echo $output;
        } else {
            echo "Error loading error view.";
        }
    }
}