<?php
namespace App\Controllers;

use Framework\Exceptions\HttpException,
    Framework\Controller,
    Framework\Response,
    Framework\Session,
    App\FormRequests\ListingsFormRequest,
    App\Models\Listings;

class ListingsController extends Controller {

    public function __construct(
        private Listings $listings,
        private ListingsFormRequest $formRequest,
        private Session $session
    ) {}


    /**
     * Display a list of all job listings.
     *
     * @return Response
     */
    public function index(): Response {
        if ($listings = $this->listings->findAll()) {
            return $this->view(
                'listings/index', ['title' => 'Listings', 'listings' => $listings]
            );
        } else {
            throw new HttpException(404, 'Sorry, no jobs found');
        }
    }


    /**
     * Display the details of a specific job listing.
     *
     * @param int $job_id
     * @return Response
     */
    public function show(int $job_id): Response {
        if ($job = $this->listings->findOne($job_id)) {
            return $this->view('listings/show', ['title' => 'Job Details', 'job' => $job]);
        } else {
            throw new HttpException(404, 'Sorry, that job doesn\'t exist', '/listings');
        }
    }


    /**
     * Display the form to create a new job listing.
     *
     * @return Response
     */
    public function create(): Response {
        return $this->view('listings/create');
    }


    /**
     * Display the form to edit an existing job listing.
     *
     * @param int $job_id
     * @return Response
     */
    public function edit(int $job_id): Response {
        if ($job = $this->listings->findOne($job_id)) {
            return $this->view('listings/create', ['job' => $job]);
        } else {
            throw new HttpException(404, 'Sorry, that job doesn\'t exist', '/listings');
        }
    }


    /**
     * Store a new job listing in the database.
     *
     * @return Response
     */
    public function store(): Response {
        $formRequest = $this->formRequest;

        if ($formRequest->validate()) {
            $listing = $formRequest->sanitized();
            $listing['user_id'] = 1; // temporary user ID for testing

            if ($this->listings->create($listing)) {
                $this->session->success('Listing successfully created.');
                return $this->redirect('/listings');
            } else {
                throw new HttpException(
                    500, 'Sorry, there was a problem creating the job listing', '/listings/create'
                );
            }
        } else {
            return $this->view(
                'listings/create',
                [
                    'errors' => $formRequest->getErrors(),
                    'job' => (object) $formRequest->getRequest()->post
                ]
            );
        }
    }


    /**
     * Update an existing job listing in the database.
     *
     * @param int $job_id
     * @return Response
     */
    public function update(int $job_id): Response {

        if (!$this->listings->findOne($job_id)) {
            throw new HttpException(404, "Sorry, that job doesn't exist", '/listings');
        }

        $formRequest = $this->formRequest;

        if ($formRequest->validate()) {

            $listing = $formRequest->sanitized();
            $listing['user_id'] = 1; // temporary user ID for testing

            if ($this->listings->update($job_id, $listing)) {
                $this->session->success('Listing successfully updated.');
                return $this->redirect("/listings/{$job_id}");
            } else {
                throw new HttpException(
                    500, 'Sorry, there was a problem updating the job listing', "/listings/{$job_id}/edit"
                );
            }

        } else {

            $post_data = $formRequest->getRequest()->post;
            $errors = $formRequest->getErrors();

            return $this->view(
                'listings/create',
                [
                    'errors' => $errors,
                    'job' => (object)[...$post_data, 'id' => $job_id]
                ]
            );
        }
    }


    /**
     * Delete a job listing from the database.
     *
     * @param int $job_id
     * @return Response
     */
    public function delete(int $job_id): Response {
        if ($this->listings->delete($job_id)) {
            $this->session->success('Listing successfully deleted.');
            return $this->redirect('/listings');
        } else {
            throw new HttpException(
                404, 'Sorry, that listing could not be deleted', '/listings'
            );
        }
    }
}
