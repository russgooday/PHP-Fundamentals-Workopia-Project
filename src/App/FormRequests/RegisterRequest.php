<?php

namespace App\FormRequests;

use Framework\FormRequest;

class RegisterRequest extends FormRequest {

    const PASSWORD =
        '^'
        . '(?=.*[A-Z])' // at least 1 uppercase letter
        . '(?=.*[a-z])' // at least 1 lowercase letter
        . '(?=.*[0-9])' // at least 1 number
        . '(?=.*[-@_$!%*?&])' // at least 1 symbol
        . '[-A-Za-z0-9@_$!%*?&]{8,25}'
        .'$';

    protected array $rules = [
        'name'                  => ['required', 'max:255'],
        'email'                 => ['required', 'email', 'max:45'],
        'city'                  => ['required', 'max:255'],
        'state'                 => ['required', 'max:255'],
        'password'              => ['required', 'pattern:' . self::PASSWORD],
        'password_confirmation' => ['required', 'same:password']
    ];


    public function messages(): array {
        return [
            'password.pattern' => fn() => (
                'The password must be 8 to 25 characters and contain at '
                . 'least 1 uppercase, 1 lowercase, 1 number, and 1 symbol.'
            ),
            'password_confirmation.same' => fn() => (
                'The password confirmation must match the password.'
            )
        ];
    }


    public function sanitize(): array {
        return [
            'password' => fn($value) => password_hash($value, PASSWORD_DEFAULT)
        ];
    }
}