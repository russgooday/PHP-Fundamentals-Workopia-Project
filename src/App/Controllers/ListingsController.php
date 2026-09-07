<?php
namespace App\Controllers;

use Framework\Exceptions\HttpException,
    Framework\Controller,
    App\Models\Listings;

class ListingsController extends Controller {

    public function __construct(
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
            echo 'Sorry no jobs';
        }
    }


    public function show(int $job_id): void {
        if ($job = $this->listings->findOne($job_id)) {
            echo ($this->viewer->render(
                'listings/show', ['title' => 'Job Details', 'job' => $job]
            ));
        } else {
            throw new HttpException(
                404, "Sorry, that job doesn't exist", "/listings"
            );
            // echo $this->error(404, "Sorry, that job doesn't exist");
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
            throw new HttpException(404, 'Create a job view not found');
        }
    }
}
