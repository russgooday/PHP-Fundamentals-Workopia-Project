<?php
namespace App\Controllers;

use Framework\Exceptions\HttpException,
    Framework\Controller,
    Framework\Response,
    Framework\Session;

use App\Models\Users,
    App\FormRequests\UserFormRequest;


class UserController extends Controller {

    public function __construct(
        private Users $users,
        private UserFormRequest $formRequest,
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