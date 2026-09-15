<?php
/**
 * Convert an array of keys into a comma-separated list of column names for SQL queries.
 *
 * @param array $keys The array of keys to convert.
 * @return string The comma-separated list of column names.
 */
function toColumns(array $keys): string {
    return implode(', ', $keys);
}

/**
 * Convert an array of keys into a comma-separated list of placeholders for SQL queries.
 *
 * @param array $keys The array of keys to convert.
 * @return string The comma-separated list of placeholders.
 */
function toPlaceholders(array $keys): string {
    return implode(', ', array_map(fn($key) => ":$key", $keys));
}

/**
 * Get the PDO data type for a given value.
 *
 * @param mixed $value The value to get the PDO data type for.
 * @return int The PDO data type constant.
 */
function getDataType(mixed $value): int {
    static $types = [
        'boolean' => PDO::PARAM_BOOL,
        'integer' => PDO::PARAM_INT,
        'NULL'    => PDO::PARAM_NULL,
    ];

    return $types[gettype($value)] ?? PDO::PARAM_STR;
}
