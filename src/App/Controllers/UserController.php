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


    /**
     * Handle the submission of the user registration form.
     *
     * @param RegisterRequest $form The form request containing the registration data.
     * @return Response
     */
    public function store(RegisterRequest $form) {
        // pick only these selected fields ('id' will be ignored in post data)
        $picked_fields = pick('id', 'name', 'email', 'city', 'state');

        if ($form->validate()) {
            // Sanitize the form data including password before storing it
            $user = $picked_fields($this->users->create($form->sanitized()));

            $this->session->regenerateId();
            $this->session->set('user', $user);
            $this->session->success('Registration successful. You are now logged in.');

            return $this->redirect('/');
        } else {
            return $this->create([
                'errors' => $form->getErrors(),
                'user' => (object) $picked_fields($this->request->post)
            ]);
        }
    }
}