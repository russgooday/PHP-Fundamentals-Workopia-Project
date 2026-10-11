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
    foreach ($needles as $needle) {
        if (in_array($needle, $haystack, true)) {
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
    $filtered = [];
    $flipped = array_flip($keys);

    foreach($arr as $key => $val) {
        if (isset($flipped[$key])) {
            $filtered[$key] = $val;
        }
    }

    return $filtered;
}


/**
 * Polyfill for array_is_list() for PHP versions < 8.1.
 *
 * @param array $array The array to check.
 * @return bool True if the array is a list, false otherwise.
 */
if (!function_exists('array_is_list')) {
    function array_is_list(array $array): bool
    {
        $i = 0;
        foreach ($array as $k => $v) {
            if ($k !== $i++) {
                return false;
            }
        }
        return true;
    }
}


/**
 * Sorts an array using the insertion sort algorithm and a custom comparison function.
 *
 * @param array $arr The array to sort.
 * @param callable $fn The comparison function, which should return a negative, zero, or positive value.
 * @return array The sorted array.
 */
function insertSort(array $arr, callable $fn): array {
    // Ensure the array is a sequential list.
    if (!array_is_list($arr)) {
        $arr = array_values($arr);
    }

    $len = count($arr);
    $i = 1;

    while ($i < $len) {
        $temp = $arr[$i];
        $j = $i - 1;

        while ($j >= 0 && $fn($arr[$j], $temp) > 0) {
            $arr[$j + 1] = $arr[$j]; // shift right
            $j--;
        }

        $arr[$j + 1] = $temp;
        $i++;
    }

    return $arr;
}
