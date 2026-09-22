<?php

namespace App\FormRequests;

use Framework\FormRequest;

class ListingsFormRequest extends FormRequest {

    protected array $rules = [
        'title'         => ['required'],
        'description'   => ['required'],
        'salary'        => ['required', 'numeric'],
        'tags'          => ['nullable'],
        'company'       => ['required'],
        'address'       => ['nullable'],
        'city'          => ['nullable'],
        'state'         => ['nullable'],
        'phone'         => ['nullable'],
        'email'         => ['required', 'email'],
        'requirements'  => ['required'],
        'benefits'      => ['nullable'],
    ];

    protected array $sanitize_overrides = ['salary' => 'toFloat'];
}