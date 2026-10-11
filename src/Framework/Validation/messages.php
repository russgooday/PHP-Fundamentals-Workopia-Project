<?php
return [
    'required' => fn(string $name) => "The {$name} field is required.",
    'string' => fn(string $name) => "The {$name} field must be a string.",
    'numeric'=> fn(string $name) => "The {$name} field must be a number.",
    'min' => [
        'numeric' => fn(string $name, int|float $min) => (
            "The {$name} field must be at least {$min}."
        ),
        'string' => fn(string $name, int|float $min) => (
            "The {$name} field must be at least {$min} characters."
        )
    ],
    'max' => [
        'numeric' => fn(string $name, int|float $max) => (
            "The {$name} field must be at most {$max}."
        ),
        'string' => fn(string $name, int|float $max) => (
            "The {$name} field must be at most {$max} characters."
        )
    ],
    'between' => [
        'numeric' => fn(string $name, int|float $min, int|float $max) => (
            "The {$name} field must be a number between {$min} and {$max}."
        ),
        'string' => fn(string $name, int|float $min, int|float $max) => (
            "The {$name} field must be between {$min} and {$max} characters."
        )
    ],
    'array' => fn(string $name) => "The {$name} field contains invalid keys.",
    'email' => fn(string $name) => "The {$name} field must be a valid email.",
    'default' => fn(string $name) => "The {$name} field is invalid."
];
