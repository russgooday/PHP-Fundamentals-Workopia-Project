<?php
include_once basePath('src/App/config/HttpErrorMessages.php');
$messages = new HttpErrorMessages();

if ($output = loadView(
    'error', $messages->fetchError($status_code ?? 500)
))
    echo $output;
else
    echo "Error loading error view.";
