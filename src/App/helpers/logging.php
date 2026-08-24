<?php
require_once __DIR__ . '/path.php';

/**
 * Log an error message to the error log file
 *
 * @param string $message
 * @return void
 */
function logError(string $message): void {
    $errorLog = basePath('logs/error.log');
    $timestamp = date('Y-m-d H:i:s');
    $requestUri = $_SERVER['REQUEST_URI'] ?? 'CLI';

    error_log("[{$timestamp}] [uri: {$requestUri}] {$message}\n", 3, $errorLog);
}
