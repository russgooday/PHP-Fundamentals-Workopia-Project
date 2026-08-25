<?php
use Framework\Template;

$template = new Template();

if ($output = $template->render('home',
    [
        'title' => 'Home',
        'search' => $_GET['search'] ?? null
    ]
)) {
    echo $output;
} else {
    echo "Error loading home view.";
}
