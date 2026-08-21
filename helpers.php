<?php
/**
 * Get the base path
 *
 * @param string $path
 * @return string
 */
function basePath(string $path = ''): string {
    if (!$path) return __DIR__;

    $sep = DIRECTORY_SEPARATOR;

    return __DIR__
        . $sep
        . ltrim(str_replace(['\\', '/'], $sep, $path), $sep);
}

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

/**
 * Inspect a value(s) and output it in a
 * readable format for debugging
 *
 * @param mixed $value
 * @return void
 */
function inspect(mixed $value): void {
    echo '<pre>';
    var_dump($value);
    echo '</pre>';
}

/**
 * Inspect a value(s) and die
 *
 * @param mixed $value
 * @return void
 */
function inspectAndDie(mixed $value): void {
    inspect($value);
    die();
}

/**
 * Load a view file
 *
 * @param string $name
 * @param array<string,mixed> $data Optional variables extracted into view scope; defaults to [].
 * @return void
 */
function loadView(string $name, array $data = []): void {
    $viewPath = basePath("views/{$name}.view.php");

    if (file_exists($viewPath)) {
        extract($data, EXTR_SKIP /* do not overwrite existing variables */);
        require $viewPath;
    } else {
        logError("View file '{$name}' not found at: {$viewPath}");
    }
}


/**
 * Load partial file
 *
 * @param string $name name of the partial file to load
 * @param array<string,mixed> $data Optional variables extracted into view scope; defaults to [].
 * @return void
 */
function loadPartial(string $name, array $data = []): void {
    $partialPath = basePath("views/partials/{$name}.php");

    if (file_exists($partialPath)) {
        extract($data, EXTR_SKIP);
        require $partialPath;
    } else {
        logError("Partial file '{$name}' not found at: {$partialPath}");
    }
}


/**
 * Load partial files
 *
 * @param string[] $names names of the partial files to load
 * @param array<string,mixed> $data Optional variables extracted into view scope; defaults to [].
 * @return void
 */
function loadPartials(array $names, array $data = []): void {
    extract($data, EXTR_SKIP);

    foreach ($names as $name) {
        $partialPath = basePath("views/partials/{$name}.php");

        if (file_exists($partialPath)) {
            require $partialPath;
        } else {
            logError("Partial file '{$name}' not found at: {$partialPath}");
            inspectAndDie("Partial file '{$name}' not found at: {$partialPath}");
        }
    }
}
