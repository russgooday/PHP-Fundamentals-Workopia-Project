<?php
use Framework\Template;
use App\Models\Listings;

$template = new Template();
$listings = new Listings();

if ($output = $template->render('home',
    [
        'title' => 'Home',
        'listings' => $listings->findAll(6),
        // 'search' => $_GET['search'] ?? null
    ]
)) {
    echo $output;
} else {
    echo "Error loading home view.";
}
