<?php

use Framework\Template;

$template = new Template();

if ($output = $template->render(
    'listings/index', ['title' => 'Listings']
)) {
    echo $output;
} else {
    echo "Error loading listings view.";
}
