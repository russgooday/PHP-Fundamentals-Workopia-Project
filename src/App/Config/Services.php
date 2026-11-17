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
use Framework\Providers\ServiceProvider;

class Services extends ServiceProvider {

    public function register(Container $container): void {

        $db_config = parse_ini_file(Paths::ROOT . '/.env');

        $container
            ->singleton(Database::class, fn() => new Database(...$db_config))
            ->singleton(ViewerInterface::class, fn() => new PHPViewer());

        $container
            // Set viewer on the base controller
            ->afterResolving(
                Controller::class,

                function($controller, $container) {
                    if (method_exists($controller, 'setViewer')) {
                        $controller->setViewer($container[ViewerInterface::class]);
                    }
                }
            )
            // share the session globally in view
            ->afterResolving(
                ViewerInterface::class,

                function($viewer, $container) {
                    $viewer->share('session', $container[Session::class]);
                }
            );
    }
}
