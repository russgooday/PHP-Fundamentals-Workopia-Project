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


    /**
     * Display a list of all job listings.
     *
     * @return void
     */
    public function index(): void {
        if ($listings = $this->listings->findAll()) {
            echo ($this->view(
                'listings/index', ['title' => 'Listings', 'listings' => $listings]
            ));
        } else {
            throw new HttpException(404, 'Sorry, no jobs found');
        }
    }


    /**
     * Display the details of a specific job listing.
     *
     * @param int $job_id
     * @return void
     */
    public function show(int $job_id): void {
        if ($job = $this->listings->findOne($job_id)) {
            echo ($this->view('listings/show', ['title' => 'Job Details', 'job' => $job]));
        } else {
            throw new HttpException(404, 'Sorry, that job doesn\'t exist', '/listings');
        }
    }


    /**
     * Display the form to create a new job listing.
     *
     * @return void
     */
    public function create(): void {
        echo $this->view('listings/create');
    }

    /**
     * Display the form to edit an existing job listing.
     *
     * @param int $job_id
     * @return void
     */
    public function edit(int $job_id): void {
        if ($job = $this->listings->findOne($job_id)) {
            echo ($this->view('listings/create', ['job' => $job]));
        } else {
            throw new HttpException(404, 'Sorry, that job doesn\'t exist', '/listings');
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
            $listing = $formRequest->sanitized();
            $listing['user_id'] = 1; // temporary user ID for testing

            if ($this->listings->create($listing)) {
                $this->redirect('/listings');
            } else {
                throw new HttpException(
                    500, 'Sorry, there was a problem creating the job listing', '/listings/create'
                );
            }
        } else {
            echo $this->view(
                'listings/create',
                [
                    'errors' => $formRequest->getErrors(),
                    'job' => (object) $formRequest->getRequest()->post
                ]
            );
        }
    }


    public function update(int $job_id): void {
        $formRequest = $this->formRequest;

        if ($formRequest->validate()) {
            $listing = $formRequest->sanitized();
            $listing['user_id'] = 1; // temporary user ID for testing

            if ($this->listings->update($job_id, $listing)) {
                $this->redirect('/listings');
            } else {
                throw new HttpException(
                    500, 'Sorry, there was a problem updating the job listing', "/listings/{$job_id}/edit"
                );
            }
        } else {
            $request = $formRequest->getRequest();

            echo $this->view(
                'listings/create',
                [
                    'errors' => $formRequest->getErrors(),
                    'job' => (object)[...$request->post, 'id' => $job_id]
                ]
            );
        }
    }


    /**
     * Delete a job listing from the database.
     *
     * @param int $job_id
     * @return void
     */
    public function delete(int $job_id): void {
        if ($this->listings->delete($job_id)) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Listing successfully deleted.'
            ];
            $this->redirect('/listings');
        } else {
            throw new HttpException(
                404, 'Sorry, that listing could not be deleted', '/listings'
            );
        }
    }
}
