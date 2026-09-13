<?php

namespace App\FormRequests;

use Framework\FormRequest;

class ListingsFormRequest extends FormRequest {

    protected array $rules = [
        'title'         => ['required', 'max:10'],
        'description'   => ['required'],
        'salary'        => ['required', 'numeric', 'between:15000,200000'],
        'tags'          => ['required'],
        'company'       => ['required'],
        'address'       => ['required'],
        'city'          => ['required'],
        'state'         => ['required'],
        'phone'         => ['required'],
        'email'         => ['required', 'email'],
        'requirements'  => ['required'],
        'benefits'      => ['nullable'],
    ];

}