<?php
require_once __DIR__ . '/autoloader.php';

$auto_loader = (new Autoloader())

    ->addNamespace('App\\', 'src/App/')
    ->addNamespace('Framework\\', 'src/Framework/')
    ->addNamespace('Symfony\\Component\\VarDumper\\', 'vendor/symfony/var-dumper/')

    ->loadFile('vendor/symfony/var-dumper/Resources/functions/dump.php')
    ->loadFile('src/Framework/functions.php')
    ->loadFile('src/App/helpers.php')

    ->register();