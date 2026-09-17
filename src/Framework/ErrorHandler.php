<?php
namespace Framework;

use App\Controllers\ErrorController,
    Framework\Exceptions\HttpException;

class ErrorHandler {
    protected ErrorController $error_controller;

    public function __construct(protected Container $container) {
        $this->error_controller = $this
            ->container->resolve(ErrorController::class)
            ->setViewer($this->container->resolve(ViewerInterface::class));
    }

    public function handleException(\Throwable $e): void {
        if ($e instanceof HttpException) {

            $this->error_controller->index($e->getCode(), $e->getMessage(), $e->getReturnUrl());
        } else if ($e instanceof \PDOException) {

            logError("A database error occurred: " . $e->getMessage());
            $this->error_controller->index($e->getCode(), $e->getMessage());
        } else {

            logError("Fatal Core Failure: {$e->getMessage()} in {$e->getFile()} on line {$e->getLine()}");
            $this->error_controller->index($e->getCode(), $e->getMessage());
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