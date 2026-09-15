<?php

namespace App\FormRequests;

use Framework\FormRequest;

class ListingsFormRequest extends FormRequest {

    protected array $rules = [
        'title'         => ['required'],
        'description'   => ['required'],
        'salary'        => ['required', 'numeric', 'between:10000,1000000'],
        'tags'          => ['nullable'],
        'company'       => ['required'],
        'address'       => ['required'],
        'city'          => ['required'],
        'state'         => ['required'],
        'phone'         => ['required'],
        'email'         => ['required', 'email'],
        'requirements'  => ['required'],
        'benefits'      => ['nullable'],
    ];

    protected array $sanitize_overrides = ['salary' => 'toFloat'];
}