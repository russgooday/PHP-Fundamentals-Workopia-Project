<?php
/**
 * Convert an array of keys into a comma-separated list of column names for SQL queries.
 *
 * @param array $keys The array of keys to convert.
 * @return string The comma-separated list of column names.
 */
function sqlColumns(array $keys): string {
    return implode(', ', $keys);
}

/**
 * Convert an array of keys into a comma-separated list of placeholders for SQL queries.
 *
 * @param array $keys The array of keys to convert.
 * @return string The comma-separated list of placeholders.
 */
function sqlInsertParams(array $keys): string {
    return implode(', ', array_map(fn($key) => ":$key", $keys));
}

/**
 * Convert an array of keys into a comma-separated list of update placeholders for SQL queries.
 *
 * @param array $keys The array of keys to convert.
 * @return string The comma-separated list of update placeholders.
 */
function sqlUpdateParams(array $keys): string {
    return implode(', ', array_map(fn($key) => "$key=:$key", $keys));
}

/**
 * Get the PDO data type for a given value.
 *
 * @param mixed $value The value to get the PDO data type for.
 * @return int The PDO data type constant.
 */
function pdoDataType(mixed $value): int {
    static $types = [
        'boolean' => PDO::PARAM_BOOL,
        'integer' => PDO::PARAM_INT,
        'NULL'    => PDO::PARAM_NULL,
    ];

    return $types[gettype($value)] ?? PDO::PARAM_STR;
}

/**
 * Strip control characters from a string and return null if the result is empty.
 *
 * @param string|null $value The string to strip and nullify.
 * @return string|null The stripped string or null if empty.
 */
function stripAndNullify(?string $value): ?string {
    $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value);
    return $value === '' ? null : $value;
}
