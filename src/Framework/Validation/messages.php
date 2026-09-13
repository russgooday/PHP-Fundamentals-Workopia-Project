<?php
return [
    'required' => fn(string $field) => "The {$field} field is required.",
    'string' => fn(string $field) => "The {$field} field must be a string.",
    'numeric'=> fn(string $field) => "The {$field} field must be a number.",
    'min' => [
        'numeric' => fn(string $field, int|float $min) => (
            "The {$field} field must be at least {$min}."
        ),
        'string' => fn(string $field, int|float $min) => (
            "The {$field} field must be at least {$min} characters."
        )
    ],
    'max' => [
        'numeric' => fn(string $field, int|float $max) => (
            "The {$field} field must be at most {$max}."
        ),
        'string' => fn(string $field, int|float $max) => (
            "The {$field} field must be at most {$max} characters."
        )
    ],
    'between' => [
        'numeric' => fn(string $field, int|float $min, int|float $max) => (
            "The {$field} field must be a number between {$min} and {$max}."
        ),
        'string' => fn(string $field, int|float $min, int|float $max) => (
            "The {$field} field must be between {$min} and {$max} characters."
        )
    ],
    'email' => fn(string $field) => "The {$field} field must be a valid email.",
    'default' => fn(string $field) => "The {$field} field is invalid."
];
