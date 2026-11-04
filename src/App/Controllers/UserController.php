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
     * @param array $additionalData Additional data to pass to the view.
     *
     * @return Response
     */
    public function create(array $additionalData = []) {
        $data = [
            'title' => 'Register',
            'scripts' => ['/js/form.js'],
            ...$additionalData
        ];

        return $this->view('users/register', $data);
    }

    /**
     * Display the user login form.
     *
     * @return Response
     */
    public function login() {
        return $this->view('users/login', ['title' => 'Login']);
    }



    public function store(RegisterRequest $form) {
        if ($form->validate()) {

            $form_data = $form->sanitized();
            $params = ['email' => $form_data['email']];

            $user = $this->users->query('SELECT * FROM users WHERE email = :email', $params);

            if ($user = $user->fetch()) {
                // user already exists
                $this->session->error('User with this email already exists.');

                return $this->redirect('/auth/register');
            }

            $this->users->create($form_data);

            $this->session->success('User successfully registered.');

            return $this->redirect('/auth/login');
        } else {
            // pick only select fields to repopulate the form with
            $fields = pick('name', 'email', 'city', 'state');

            return $this->create([
                'errors' => $form->getErrors(),
                'user' => (object) $fields($this->request->post)
            ]);
        }
    }
}