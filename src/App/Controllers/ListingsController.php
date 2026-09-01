<?php
namespace App\Controllers;
use Framework\Controller;
use Framework\Viewer;
use App\Models\Listings;

class ListingsController extends Controller {

    public function __construct(
        private Viewer $viewer,
        private Listings $listings
    ) {}


    public function index(): void {

        if ($output = $this->viewer->render(
            'listings/index', [
                'title' => 'Listings',
                'listings' => $this->listings->findAll(4)
            ]
        )) {
            echo $output;
        } else {
            echo "Error loading listings view.";
        }
    }


    public function show(string $job_id): void {

        if ($output = $this->viewer->render(
            'listings/show', [
                'title' => 'Job Details',
                'job' => $this->listings->findOne($job_id)
            ]
        )) {
            echo $output;
        } else {
            echo "Error loading show a job view.";
        }
    }


    public function create(): void {

        if ($output = $this->viewer->render(
            'listings/create', [
                'title' => 'Create a Job Listing'
            ]
        )) {
            echo $output;
        } else {
            echo "Error loading create a job view.";
        }
    }
}
