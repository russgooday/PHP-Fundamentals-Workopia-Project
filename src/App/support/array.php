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

function flatMapArray(array $arr, callable $mappingFn) {
    $length = count($arr);
    $flattened = [];
    $i = 0;

    while ($i < $length)
        _push($flattened, $mappingFn($arr[$i], $i++));

    return $flattened;
}
