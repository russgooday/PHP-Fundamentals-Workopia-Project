<?php

namespace App\Models;

use Framework\Model;

class Listings extends Model {
    protected string $table = 'listings';

    protected array $fillable = [
        'title',
        'description',
        'salary',
        'tags',
        'company',
        'address',
        'city',
        'state',
        'phone',
        'email',
        'requirements',
        'benefits',
        'user_id'
    ];
}