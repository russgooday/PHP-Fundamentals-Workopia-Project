<?php
use App\Config\Paths;
/**
 * Log an error message to the error log file
 */
function logError(\Throwable $e): void {
    $errorLog = Paths::ROOT . '/logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $requestUri = $_SERVER['REQUEST_URI'] ?? 'CLI';

    $message = (new \ReflectionClass($e))->getShortName() . ": "
        . "{$e->getMessage()}\n"
        . "Thrown in: {$e->getFile()} on line {$e->getLine()}\n"
        . "Stack trace:\n"
        . preg_replace('/^/m', ' - ', $e->getTraceAsString()) . PHP_EOL;


    error_log("[{$timestamp}] [uri: {$requestUri}] {$message}", 3, $errorLog);
}
