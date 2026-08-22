<?php
// add GET routes

$router
    ->get('/', 'controllers/home.php')
    ->get('/listings', 'controllers/listings/index.php')
    ->get('/listings/create', 'controllers/listings/create.php')
    ->get('404', 'controllers/error/404.php');