<?php

/**
 * Inspect a value(s) and output it in a
 * readable format for debugging
 *
 * @param mixed $value
 * @return void
 */
function inspect(mixed $value): void {
    echo '<pre>';
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
function inspectAndDie(mixed $value): void {
    inspect($value);
    die();
}
