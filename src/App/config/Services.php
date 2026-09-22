<?php
namespace App\Config;
use
    Framework\Container,
    Framework\Database,
    Framework\Request,
    Framework\Validation\MessageLoader;

class Services {
    public static function register(Container $container, Request $request): Container {
        $container
            ->register(Database::class, fn() => new Database(...parse_ini_file(Paths::ROOT . '/.env')))
            ->register(MessageLoader::class, fn() => new MessageLoader(Paths::FRAMEWORK . '/Validation/messages.php'))
            ->register(Request::class, fn() => $request);

        return $container;
    }
}