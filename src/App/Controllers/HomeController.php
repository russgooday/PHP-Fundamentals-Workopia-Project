<?php
namespace App\Controllers;

use Framework\Exceptions\HttpException,
    Framework\Controller,
    Framework\Response,
    App\Models\Listings;

class HomeController extends Controller {

    public function __construct(
        protected Listings $listings
    ) {}

    public function index(): Response {
        if ($response = $this->view('home',
            [
                'title' => 'Home',
                'listings' => $this->listings->findAll(6),
                // 'search' => $_GET['search'] ?? null
            ]
        )) {
            return $response;
        } else {
            throw new HttpException(404, 'Page not found');
        }
    }
}