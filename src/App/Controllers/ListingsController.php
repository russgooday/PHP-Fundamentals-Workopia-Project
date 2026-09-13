<?php
namespace App\Controllers;

use Framework\Exceptions\HttpException,
    Framework\Controller,
    App\FormRequests\ListingsFormRequest,
    App\Models\Listings;

class ListingsController extends Controller {

    public function __construct(
        private Listings $listings,
        private ListingsFormRequest $formRequest
    ) {}


    public function index(): void {
        if ($listings = $this->listings->findAll(4)) {
            echo ($this->viewer->render(
                'listings/index', ['title' => 'Listings', 'listings' => $listings]
            ));
        } else {
            throw new HttpException(404, "Sorry, no jobs found");
        }
    }


    public function show(int $job_id): void {
        if ($job = $this->listings->findOne($job_id)) {

            echo ($this->viewer->render(
                'listings/show', ['title' => 'Job Details', 'job' => $job]
            ));
        } else {
            throw new HttpException(404, "Sorry, that job doesn't exist", "/listings");
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
            throw new HttpException(404, 'Create a job view not found', "/listings");
        }
    }


    /**
     * Store a new job listing in the database.
     *
     * @return void
     */
    public function store(): void {
        $this->formRequest->validate();
        inspectAndDie($this->formRequest->getErrors());
        // $data = $this->request->getPostData();

        // if ($this->listings->create($data)) {
        //     redirect('/listings');
        // } else {
        //     throw new HttpException(500, "Sorry, there was a problem creating the job listing", "/listings/create");
        // }
    }
}
