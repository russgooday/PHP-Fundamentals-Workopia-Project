<?php

/**
 * Escape dynamic output for safe HTML rendering.
 * @param mixed $value
 * @return string
 */
function e(mixed $value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Format a number as a currency string in USD.
 * @param float $amount
 * @param int $precision
 * @return string
 */
function toDollars(int|float $amount, int $precision = 0): string {
    static $formatter = new NumberFormatter('en-US', NumberFormatter::CURRENCY);
    $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $precision);

    return $formatter->formatCurrency($amount, 'USD');
}

/**
 * Join an array of strings using a callback and a separator.
 * @param callable $callback A callback function to apply to each element.
 * @param array $array The array of strings to join.
 * @param string $separator The separator to use between joined strings. Default is newline.
 * @return string The joined string.
 */
function joinStrings(callable $callback, array $array, string $separator = "\n"): string {
    return implode($separator, array_map($callback, $array));
}
