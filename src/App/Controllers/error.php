<?php

$messages = new APP\Config\HttpErrorMessages();

if ($output = loadView(
    'error', $messages->fetchError($status_code ?? 500)
))
    echo $output;
else
    echo "Error loading error view.";
