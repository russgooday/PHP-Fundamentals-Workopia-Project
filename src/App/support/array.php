<?php

function _push(array &$arr, mixed $value): void {
    if (is_array($value)) {
        $length = count($value);
        $i = 0;

        while ($i < $length)
            $arr[] = $value[$i++];
    } else {
        $arr[] = $value;
    }
}

if (!function_exists('array_flat_map')) {
    function array_flat_map(callable $mappingFn, array $arr): array {
        $length = count($arr);
        $flattened = [];
        $i = 0;

        while ($i < $length)
            _push($flattened, $mappingFn($arr[$i], $i++));

        return $flattened;
    }
}

// polyfill for PHP < 8.5
// see https://wiki.php.net/rfc/array_first_last
if (!function_exists('array_last')) {
    function array_last(array $array): mixed {
        return $array[array_key_last($array)];
    }
}

/**
 * Finds the first element in $haystack that matches any of the $needles.
 *
 * @param array $needles The values to search for.
 * @param array $haystack The array to search within.
 * @param mixed $default The default value to return if none of the needles are found.
 * @return mixed The first matching needle, or the default value if none are found.
 */
function findOneOf(array $needles, array $haystack, mixed $default = null): mixed {
    $flipped = array_flip($haystack);

    foreach($needles as $needle) {
        if (isset($flipped[$needle])) {
            return $needle;
        }
    }
    return $default;
}


/**
 * Filters an array by the specified keys, returning only the allowed key-value pairs.
 *
 * @param array $arr The array to filter.
 * @param array $keys The keys to allow.
 * @return array The filtered array containing only the allowed key-value pairs.
 */
function filter_by_keys(array $arr, array $keys): array {
    $allowed = [];
    $flipped = array_flip($keys);

    foreach($arr as $key => $val) {
        if (isset($flipped[$key])) {
            $allowed[$key] = $val;
        }
    }

    return $allowed;
}