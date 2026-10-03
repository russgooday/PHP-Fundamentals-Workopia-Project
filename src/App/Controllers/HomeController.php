<?php
namespace App\Controllers;

use Framework\Exceptions\HttpException;
use Framework\{Controller, Response};
use App\Models\Listings;

class HomeController extends Controller {

    public function __construct(
        protected Listings $listings
    ) {}

    public function index(): Response {
        return $this->view('home', [
            'title' => 'Home',
            'listings' => $this->listings->findAll(6),
        ]);
    }
}
