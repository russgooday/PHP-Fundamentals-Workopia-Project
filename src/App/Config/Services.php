<?php
namespace App\Config;
use Framework\{
    Response,
    Database,
    Controller,
    Session,
    ViewerInterface,
    PHPViewer
};
use Framework\Container\Container;
use Framework\Validation\MessageLoader;

class Services {

    public static function register(Container $container): Container {

        $db_config = parse_ini_file(Paths::ROOT . '/.env');
        $validation_msgs_path = Paths::FRAMEWORK . '/Validation/messages.php';

        $container
            ->singleton(Session::class, fn() => new Session())
            ->singleton(Database::class, fn() => new Database(...$db_config))
            ->singleton(MessageLoader::class, fn() => new MessageLoader($validation_msgs_path))
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
