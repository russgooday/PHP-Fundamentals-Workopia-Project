<?php
// Front controller for the application

use Framework\Request;

require_once __DIR__ . '/../bootstrap/app.php';

$response = $app->handle(Request::createFromGlobals());
$response->send();
