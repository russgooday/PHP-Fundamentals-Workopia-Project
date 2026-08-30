<?php
namespace App\Controllers;

use Framework\Controller;
use Framework\Template;
use App\Models\Listings;

class HomeController extends Controller {

    public function __construct(
        protected Template $template = new Template(),
        protected Listings $listings = new Listings()
    ) {}

    public function index(): void {
        if ($output = $this->template->render('home',
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