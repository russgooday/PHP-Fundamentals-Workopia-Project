<?php
namespace Framework;

use App\Controllers\ErrorController,
    Framework\Exceptions\HttpException;

class ErrorHandler {
    public function __construct(protected ErrorController $error_controller) {}

    public function handleException(\Throwable $e): void {
        $error_ctrl = $this->error_controller;

        $file = basename($e->getFile());
        $message = "{$e->getMessage()} in {$file} on line {$e->getLine()}";

        if ($e instanceof HttpException) {
            logError("HTTP Exception: " . $e->getMessage());
            $error_ctrl->index($e->getCode(), $message, $e->getReturnUrl());
        } else if ($e instanceof \PDOException) {

            logError("A database error occurred: " . $e->getMessage());
            $error_ctrl->index($e->getCode(), $message);
        } else {

            logError("Fatal Core Failure: {$e->getMessage()} in {$e->getFile()} on line {$e->getLine()}");
            $error_ctrl->index($e->getCode(), $message);
        }
    }

    public function handleError(int $errno, string $message, string $file, int $line): void {
        // If the error reporting is turned off via @ operator, ignore it
        if (!(error_reporting() & $errno)) {
            return;
        }

        throw new \ErrorException($message, 0, $errno, $file, $line);
    }
}
