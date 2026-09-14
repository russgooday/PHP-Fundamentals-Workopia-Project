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
            echo ($this->view(
                'listings/index', ['title' => 'Listings', 'listings' => $listings]
            ));
        } else {
            throw new HttpException(404, "Sorry, no jobs found");
        }
    }


    public function show(int $job_id): void {
        if ($job = $this->listings->findOne($job_id)) {

            echo ($this->view(
                'listings/show', ['title' => 'Job Details', 'job' => $job]
            ));
        } else {
            throw new HttpException(404, "Sorry, that job doesn't exist", "/listings");
        }
    }


    public function create(): void {

        if ($output = $this->view(
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
        $formRequest = $this->formRequest;

        if ($formRequest->validate()) {
            inspectAndDie($formRequest->sanitized());
        } else {
            echo $this->view(
                'listings/create',
                [
                    'errors' => $formRequest->getErrors(),
                    'listings' => $formRequest->getRequest()->post
                ]
            );
        }

        // $data = $this->request->getPostData();

        // if ($this->listings->create($data)) {
        //     redirect('/listings');
        // } else {
        //     throw new HttpException(500, "Sorry, there was a problem creating the job listing", "/listings/create");
        // }
    }
}
