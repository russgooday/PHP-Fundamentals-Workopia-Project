<?php

/**
 * Inspect a value(s) and output it in a
 * readable format for debugging
 *
 * @param mixed $value
 * @return void
 */
function inspect(mixed $value, string $label = ''): void {
    echo '<pre>';
    if ($label) {
        echo "<strong>$label</strong>: \n";
    }
    echo (is_array($value) || is_object($value))
        ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        : var_export($value, true);
    echo '</pre>';
}


/**
 * Inspect a value(s) and die
 *
 * @param mixed $value
 * @return void
 */
function inspectAndDie(mixed $value, string $label = ''): void {
    inspect($value, $label);
    die();
}
