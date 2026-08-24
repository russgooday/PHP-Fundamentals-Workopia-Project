<?php
// add GET routes

$router
    ->get('/', '/home.php')
    ->get('/listings', '/listings/index.php')
    ->get('/listings/create', '/listings/create.php')
    ->get('/error/:status_code', '/error.php');