<?php

namespace App\FormRequests;

use Framework\FormRequest;

class LoginRequest extends FormRequest {

    protected array $rules = [
        'email'                 => ['required', 'email', 'max:45'],
        'password'              => ['required', 'min:8', 'max:25']
    ];
}