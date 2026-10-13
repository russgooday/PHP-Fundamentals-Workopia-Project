<?php
namespace App\Config;

use Framework\{
    Request,
    Response,
    Database,
    Controller,
    Session,
    ViewerInterface,
    PHPViewer
};

use Framework\Container\Container;

class Services {

    public static function register(Container $container): Container {

        $db_config = parse_ini_file(Paths::ROOT . '/.env');

        $container
            ->singleton(Session::class, fn() => new Session())
            ->singleton(Request::class, fn() => Request::createFromGlobals())
            ->singleton(Database::class, fn() => new Database(...$db_config))
            ->singleton(ViewerInterface::class, fn() => new PHPViewer());

        $container
            ->afterResolving(
                Controller::class,

                function($controller, $container) {
                    if (method_exists($controller, 'setResponse')) {
                        $controller->setResponse($container[Response::class]);
                    }

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
