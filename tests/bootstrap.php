<?php
require_once dirname(__DIR__) . '/src/App/functions.php';
require_once dirname(__DIR__) . '/autoloader.php';

(new Autoloader())
    ->addNamespace('App\\', 'src/App/')
    ->addNamespace('Framework\\', 'src/Framework/')
    ->addNamespace('Spikes\\', 'tests/Spikes/')
    ->register();