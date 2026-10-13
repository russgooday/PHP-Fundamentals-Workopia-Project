<?php

namespace App\Config;

class Middleware {
    public static function register(): array {
        return [
            // Global middlewares executed on every single request
            'global' => [
                \App\Middleware\CrossSite::class,
                \App\Middleware\ChangeResponse::class
            ],

            // Route aliases for specific route endpoints
            'aliases' => [
                'example' => \App\Middleware\HomeViewResponse::class,
            ]
        ];
    }
}