<?php
namespace App\Config;
use
    Framework\Container,
    Framework\Database,
    Framework\ViewerInterface,
    Framework\PHPViewer,
    Framework\Request,
    Framework\Validation\MessageLoader;

class Services {
    public static function register(Container $container, Request $request): Container {
        // May change target paths to actual controller classes.
        $container
            ->register(Database::class, fn() => new Database(...parse_ini_file(Paths::ROOT . '/.env')))
            ->register(ViewerInterface::class, fn() => (new PHPViewer())->share('title', 'Workopia'))
            ->register(MessageLoader::class, fn() => new MessageLoader(Paths::FRAMEWORK . '/Validation/messages.php'))
            ->register(Request::class, fn() => $request);

        return $container;
    }
}