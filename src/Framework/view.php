<?php

function outputBufferView(string $viewPath, array $data = []): string {
    extract($data, EXTR_SKIP);

    ob_start();

    include $viewPath;

    return ob_get_clean();
}


/**
 * Load a view file
 *
 * @param string $name
 * @param array<string,mixed> $data Optional variables extracted into view scope; defaults to [].
 * @return ?string
 */
function loadView(string $name, array $data = []): ?string {
    $viewPath = basePath("src/App/views/{$name}.view.php");

    if (file_exists($viewPath)) {

        return outputBufferView($viewPath, $data) ?? null;

    } else {

        logError("View file '{$name}' not found at: {$viewPath}");
        return null;
    }
}


/**
 * Load partial file
 *
 * @param string $name name of the partial file to load
 * @param array<string,mixed> $data Optional variables extracted into view scope; defaults to [].
 * @return ?string
 */
function loadPartial(string $name, array $data = []): ?string {
    $partialPath = basePath("src/App/views/partials/{$name}.php");

    if (file_exists($partialPath)) {

        return outputBufferView($partialPath, $data) ?? null;

    } else {

        logError("Partial file '{$name}' not found at: {$partialPath}");
        return null;
    }
}


/**
 * Load partial files
 *
 * @param string[] $names names of the partial files to load
 * @param array<string,mixed> $data Optional variables extracted into view scope; defaults to [].
 * @return string
 */
function loadPartials(array $names, array $data = []): string {
    $output = '';

    foreach ($names as $name) {
        $partialOutput = loadPartial($name, $data);

        if ($partialOutput !== null) {
            $output .= $partialOutput;
        }
    }

    return $output;
}


/**
 * Escape dynamic output for safe HTML rendering.
 *
 * @param mixed $value
 * @return string
 */
function e(mixed $value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}
