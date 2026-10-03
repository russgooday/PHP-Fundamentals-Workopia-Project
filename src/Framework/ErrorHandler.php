<?php
namespace Framework;

use App\Controllers\ErrorController,
    Framework\Exceptions\HttpException;

error_reporting(E_ALL);

class ErrorHandler {
    public function __construct(protected ErrorController $error_controller) {}

    public function handleException(\Throwable $e): void {
        $error_ctrl = $this->error_controller;

        $message = $e->getMessage();
        logError($e);

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
}
