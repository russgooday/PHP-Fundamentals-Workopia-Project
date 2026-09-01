<?php
namespace App\Controllers;

use Framework\Controller;
use Framework\Viewer;
use App\Models\Listings;

class HomeController extends Controller {

    public function __construct(
        protected Viewer $viewer,
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
            echo "Error loading home view.";
        }
    }
}