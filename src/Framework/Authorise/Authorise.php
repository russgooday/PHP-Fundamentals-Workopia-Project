<?php

namespace Framework\Authorise;

use Framework\Session;
use Framework\Model;

class Authorise {

    public function __construct(private Session $session){}

    public function attempt(Model $model, array $login_data): bool {
        $user = $model->findOneBy('email', $login_data['email']);

        if ($user && password_verify($login_data['password'], $user->password)) {
            $this->session->regenerateId();
            $this->session->set('user', $user->id);

            return true;
        }

        return false;
    }
}