<?php

namespace App\FormRequests;

use Framework\FormRequest;

class UserFormRequest extends FormRequest {

    protected array $rules = [
        'name'                  => ['required', 'max:255'],
        'email'                 => ['required', 'email', 'max:45'],
        'password'              => ['required', 'min:8', 'max:25'],
        'password_confirmation' => ['required', 'same:password']
    ];
}