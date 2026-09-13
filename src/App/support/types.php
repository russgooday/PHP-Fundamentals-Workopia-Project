<?php

/**
 * Cast a value to a specified type.
 * @param string|null $type The type to cast to.
 * Can be 'int', 'float', 'string', 'bool', 'array', 'object', or 'unset'.
 * If null or an unrecognized type is provided, the value is returned as-is.
 * @param mixed $val The value to be cast.
 * @return mixed The value cast to the specified type.
 */
function castTo(?string $type, mixed $val): mixed {
    return match ($type) {
        'int'    => (int) $val,
        'float'  => (float) $val,
        'string' => (string) $val,
        'bool'   => (bool) $val,
        'array'  => (array) $val,
        'object' => (object) $val,
        'unset'  => null,
        default  => $val
    };
}


/**
 * Parses a locale-aware numeric string into a float, or null if it isn't numeric.
 *
 * @param string|null $value The value to parse.
 * @return float|null The parsed float value, or null if not numeric.
 * @example: "1,234.56" will be parsed as 1234.56.
 */
function toFloat(?string $value): ?float {
    // trying to parse null will cause a fatal Error
    if ($value === null) {
        return null;
    }
    // note PHP 8.1+ allows static variables in functions
    static $formatter = new NumberFormatter('en_US', NumberFormatter::DECIMAL);
    $result = $formatter->parse($value);

    return $result === false ? null : (float) $result;
}