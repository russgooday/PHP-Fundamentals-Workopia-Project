<?php
if ($output = loadView('home',
    [
        'title' => 'Home',
        'search' => $_GET['search'] ?? null
    ]
))
    echo $output;
else
    echo "Error loading home view.";
