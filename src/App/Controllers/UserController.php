<?php
namespace App\Controllers;

use Framework\Controller,
    Framework\Response,
    Framework\Session,
    Framework\Authorise\Authorise;

use App\Models\Users,
    App\FormRequests\LoginRequest,
    App\FormRequests\RegisterRequest;


class UserController extends Controller {

    public function __construct(
        private Users $users,
        private Authorise $auth,
        private Session $session
    ) {}

    /**
     * Display the user registration form.
     * @param array $formData Errors and any previous field values.
     *
     * @return Response
     */
    public function create(array $formData = []) {
        $data = [
            'title' => 'Register',
            'scripts' => ['/js/form.js'],
            ...$formData
        ];

        return $this->view('users/register', $data);
    }

    /**
     * Display the user login form.
     * @param array $formData Errors and any previous field values.
     *
     * @return Response
     */
    public function login(array $formData = []) {
        $data = ['title' => 'Login', ...$formData];

        return $this->view('users/login', $data);
    }


    /**
     * Handle the submission of the user registration form.
     *
     * @param RegisterRequest $form The form request containing the registration data.
     * @return Response
     */
    public function store(RegisterRequest $form) {

        if ($form->validate()) {
            $this->users->create($form->sanitized());
            $this->session->success('Registration successful. Please log in.');

            return $this->redirect('/auth/login');
        } else {
            $picked_fields = pick('name', 'email', 'city', 'state');

            return $this->create([
                'errors' => $form->getErrors(),
                'user' => (object) $picked_fields($this->request->post)
            ]);
        }
    }


    public function authenticate(LoginRequest $form) {

        if ($form->validate()) {
            if ($this->auth->attempt($this->users, $form->sanitized())) {
                $this->session->success('You are successfully logged in.');

                return $this->redirect('/');
            }

            $errors = ['email' => 'Incorrect email or password!'];
        }

        return $this->login(['errors' => $errors ?? $form->getErrors()]);
    }
}