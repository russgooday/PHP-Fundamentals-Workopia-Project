<?php

namespace App\FormRequests;

use Framework\FormRequest;

class ListingsFormRequest extends FormRequest {

    protected array $rules = [
        'title'         => ['required', 'max:255'],
        'description'   => ['required', 'max:1000'],
        'salary'        => ['required', 'numeric', 'max:1000000'],
        'tags'          => ['nullable', 'max:255'],
        'company'       => ['required', 'max:255'],
        'address'       => ['nullable', 'max:255'],
        'city'          => ['nullable', 'max:255'],
        'state'         => ['nullable', 'max:255'],
        'phone'         => ['nullable', 'max:45'],
        'email'         => ['required', 'email', 'max:45'],
        'requirements'  => ['required', 'max:1000'],
        'benefits'      => ['nullable', 'max:1000']
    ];

    protected array $sanitize_overrides = ['salary' => 'toFloat'];
}