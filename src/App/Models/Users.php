<?php
namespace App\Models;

use Framework\Model;

class Users extends Model {
    protected string $table = 'users';

    protected array $fillable = [
        'name',
        'email',
        'password',
        'city',
        'state'
    ];
}