<?php
namespace App\Controllers;
use Framework\Controller;
use Framework\Template;
use App\Models\Listings;

class ListingsController extends Controller {

    public function __construct(
        protected Template $template = new Template(),
        protected Listings $listings = new Listings()
    ) {}


    public function index(): void {

        if ($output = $this->template->render(
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


    public function show(array $params): void {

        if ($output = $this->template->render(
            'listings/show', [
                'title' => 'Job Details',
                'job' => $this->listings->findOne($params['job_id'] ?? null)
            ]
        )) {
            echo $output;
        } else {
            echo "Error loading show a job view.";
        }
    }


    public function create(): void {

        if ($output = $this->template->render(
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
