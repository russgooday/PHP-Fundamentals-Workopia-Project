<?php
namespace App\Controllers;

use Framework\Exceptions\HttpException,
    Framework\Controller,
    App\Models\Listings;

class HomeController extends Controller {

    public function __construct(
        protected Listings $listings
    ) {}

    public function index(): void {
        if ($output = $this->viewer->render('home',
            [
                'title' => 'Home',
                'listings' => $this->listings->findAll(6),
                // 'search' => $_GET['search'] ?? null
            ]
        )) {
            echo $output;
        } else {
            throw new HttpException(404, 'Page not found');
        }
    }
}