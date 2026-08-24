<?php

spl_autoload_register(function (string $class_name) {

    $class_name = str_replace('\\', '/', $class_name);

    $path = __DIR__ . "/src/$class_name.php";

    if (file_exists($path)) {
        require_once $path;
    }

});