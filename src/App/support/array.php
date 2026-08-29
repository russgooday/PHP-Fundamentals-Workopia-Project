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
