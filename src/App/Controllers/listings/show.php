<?php
use Framework\Template;
use App\Models\Listings;

$template = new Template();
$listings = new Listings();

if ($output = $template->render(
    'listings/show', [
        'title' => 'Job Details',
        'job' => $listings->findOne($job_id)
    ]
)) {
    echo $output;
} else {
    echo "Error loading show a job view.";
}
