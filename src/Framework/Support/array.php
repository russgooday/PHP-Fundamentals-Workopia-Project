<?php

/**
 * Reduces an array to a single value using a callback function.
 * Unlike the built-in array_reduce if no initial value is provided,
 * the first element of the array is used as the initial accumulator.
 *
 * @param array $arr The array to reduce.
 * @param callable $reducer The callback function to apply.
 * @param mixed ...$args Optional initial value for the reduction.
 * @return mixed The reduced value.
 */
function reduceArray(array $arr, callable $reducer, mixed ...$args): mixed {
    $numItems = count($arr);
    $i = 0;

    if (count($args)) {
        $accumulator = $args[0];
    } else if ($numItems) {
        $accumulator = $arr[$i++];
    } else {
        throw new \TypeError('reduce of empty array with no initial value');
    }

    while ($i < $numItems)
        $accumulator = $reducer($accumulator, $arr[$i], $i++);

    return $accumulator;
}


/**
 * Passes a value forward through the chain of functions to the final function,
 * allowing each function to modify the value along the way.
 *
 * The result is then returned backward through the chain, where each function
 * can modify it again before it is finally returned by the pipeline.
 *
 * Each function calls its given $next function to continue the chain.
 * A function can break the chain and cause an early exit by not calling $next.
 *
 * @param array $fns The array of functions to compose.
 * @param callable $last_fn The final function to call in the pipeline.
 * @return callable A function to pass a value to the pipeline.
 *
 * @example Example of a function in the pipeline:
 *
 * function fn1($val, $next) {
 *     ... changes to $val
 *     $response = $next($val); // Calls the next function in the pipeline.
 *     ... changes to $response
 *     return $response;
 * }
 *
 * function last_fn($val) {
 *     ... changes to $val
 *     return $val;
 * }
 *
 * fn1 -> fn2 -> ... -> last_fn -> ... -> fn2 -> fn1 -> final result
 */
function pipeline(array $fns, callable $last_fn): callable {
    static $combine = fn($f, $g) =>
        fn($val, $next) =>
            $f($val, fn($val) => $g($val, $next));

    if (empty($fns)) {
        return $last_fn;
    }

    $chain = reduceArray($fns, $combine);
    return fn($val) => $chain($val, $last_fn);
}


/**
 * Pushes a value onto an array. If the value is an array, its elements are appended individually.
 * @param array $arr The array to push the value onto.
 * @param mixed $value The value to push onto the array.
 * @return void
 */
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


/**
 * Applies a mapping function to each element of an array and flattens the result into a single array.
 * @param callable $mappingFn The mapping function to apply to each element.
 * @param array $arr The array to map and flatten.
 * @return array The flattened array after applying the mapping function.
 */
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
