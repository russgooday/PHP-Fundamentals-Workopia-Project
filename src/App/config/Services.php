<?php
namespace App\Config;
use
    Framework\Container,
    Framework\Database,
    Framework\ViewerInterface,
    Framework\PHPViewer;

class Services {
    public static function register(Container $container): Container {
        // May change target paths to actual controller classes.
        $container
            ->register(Database::class, fn() => new Database(...parse_ini_file(Paths::ROOT . '/.env')))
            ->register(ViewerInterface::class, fn() => new PHPViewer());

        return $container;
    }
}