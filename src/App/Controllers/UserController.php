<?php
namespace App\Controllers;

use Framework\Exceptions\HttpException,
    Framework\Controller,
    Framework\Response,
    Framework\Session;

use App\Models\Users,
    App\FormRequests\LoginRequest,
    App\FormRequests\RegisterRequest;


class UserController extends Controller {

    public function __construct(
        private Users $users,
        private Session $session
    ) {}

    /**
     * Display the user registration form.
     *
     * @return Response
     */
    public function create() {
        return $this->view('users/register', ['title' => 'Register']);
    }

    /**
     * Display the user login form.
     *
     * @return Response
     */
    public function login() {
        return $this->view('users/login', ['title' => 'Login']);
    }
}