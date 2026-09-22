<?php

function once(callable $fn): callable {
    $hasRun = false;
    $result = null;

    return function(...$args) use ($fn, $hasRun, $result): mixed {
        if (!$hasRun) {
            $hasRun = true;
            $result = $fn(...$args);
        }
        return $result;
    };
}