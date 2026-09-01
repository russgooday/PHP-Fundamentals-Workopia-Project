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