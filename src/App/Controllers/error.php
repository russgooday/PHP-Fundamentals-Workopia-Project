<?php
use Framework\Template;
use APP\Config\HttpErrorMessages;

$template = new Template();
$messages = new HttpErrorMessages();

if ($output = $template->render(
    'error', $messages->fetchError($status_code ?? 500)
)) {
    echo $output;
} else {
    echo "Error loading error view.";
}
