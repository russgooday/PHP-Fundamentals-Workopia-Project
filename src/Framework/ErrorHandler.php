<?php
namespace Framework;

use App\Controllers\ErrorController,
    Framework\Exceptions\HttpException;

error_reporting(E_ALL);

class ErrorHandler {
    public function __construct(protected ErrorController $error_controller) {}

    public function handleException(\Throwable $e): void {
        // Note: May need try/catch here to handle potential errors
        // within the error controller itself.

        $error_ctrl = $this->error_controller;

        $message = $e->getMessage();
        $this->logError($e);

        if ($e instanceof HttpException) {
            $error_ctrl->index($e->getCode(), $message, $e->getReturnUrl());
        } else {
            $error_ctrl->index(500);
        }
    }

    public function handleError(int $errno, string $message, string $file, int $line): void {
        // If the error reporting is turned off via @ operator, ignore it
        if (!(error_reporting() & $errno)) {
            return;
        }

        throw new \ErrorException($message, 0, $errno, $file, $line);
    }

    private function logError(\Throwable $e): void {
        $error_log = dirname(__DIR__, 2) . '/logs/error.log';
        $dir = dirname($error_log);

        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $request_uri = $_SERVER['REQUEST_URI'] ?? 'CLI';

        $message = (new \ReflectionClass($e))->getShortName() . ": "
            . "{$e->getMessage()}\n"
            . "Thrown in: {$e->getFile()} on line {$e->getLine()}\n"
            . "Stack trace:\n"
            . preg_replace('/^/m', ' - ', $e->getTraceAsString()) . PHP_EOL;


        error_log("[{$timestamp}] [uri: {$request_uri}] {$message}", 3, $error_log);
    }
}
