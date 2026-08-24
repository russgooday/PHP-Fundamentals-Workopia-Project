<?php

if ($output = loadView(
    'listings/index',
    [
        'title' => 'Listings'
    ]
))
    echo $output;
else
    echo "Error loading listings view.";
