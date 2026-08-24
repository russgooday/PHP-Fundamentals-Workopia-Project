<?php
require_once '../src/App/config/Paths.php';
require_once PATHS::APP .'/functions.php';
require_once PATHS::FRAMEWORK .'/Router.php';

set_error_handler(function (int $severity, string $message, string $file, int $line): void {
    logError("Error: {$message} in {$file} on line {$line}");
});


require_once PATHS::FRAMEWORK . '/Database.php';
$db = new Database(parse_ini_file('.env'));
$db->getConnection(); // Initialize the connection on load for early error
$res = $db->query(
    <<<'SQL'
        SELECT * FROM users
        WHERE email = :email
    SQL,
    ['email' => 'user1@gmail.com']);
inspectAndDie($res->fetchAll());

$router = new Router();
require PATHS::CONFIG . '/routes.php';
$router->route($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);