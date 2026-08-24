<?php

if ($output = loadView(
    'listings/create',
    [
        'title' => 'Post a Job'
    ]
))
    echo $output;
else
    echo "Error loading post a job view.";
