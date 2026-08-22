<?php
/**
return [
    '/' => 'controllers/home.php',
    '/listings' => 'controllers/listings/index.php',
    '/listings/create' => 'controllers/listings/create.php',
    '404' => 'controllers/error/404.php'
];
*/

// GET routes

$router
    ->get('/', 'controllers/home.php')
    ->get('/listings', 'controllers/listings/index.php')
    ->get('/listings/create', 'controllers/listings/create.php')
    ->get('404', 'controllers/error/404.php');