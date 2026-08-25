<?php

use Framework\Template;

$template = new Template();

if ($output = $template->render(
    'listings/create', ['title' => 'Post a Job']
)) {
    echo $output;
} else {
    echo "Error loading post a job view.";
}
