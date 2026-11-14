<?php
namespace App\Config;

use Framework\{
    Database,
    Controller,
    Session,
    ViewerInterface,
    PHPViewer
};

use Framework\Container\Container;

class AppServices {

    public static function register(Container $container): Container {

        $db_config = parse_ini_file(Paths::ROOT . '/.env');

        $container
            ->singleton(Database::class, fn() => new Database(...$db_config))
            ->singleton(ViewerInterface::class, fn() => new PHPViewer());

        $container
            ->afterResolving(
                Controller::class,

                function($controller, $container) {
                    if (method_exists($controller, 'setViewer')) {
                        $controller->setViewer($container[ViewerInterface::class]);
                    }
                }
            )
            ->afterResolving(
                ViewerInterface::class,

                function($viewer, $container) {
                    $viewer->share('session', $container[Session::class]);
                }
            );

        return $container;
    }
}
