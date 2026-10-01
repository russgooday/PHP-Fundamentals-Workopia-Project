<?php

/**
 * Returns a curried version of the given function.
 *
 * Allows partial application of the function's arguments.
 * If more than the required number of arguments are provided,
 * the extra arguments are passed to the original function as well.
 * This allows for variadic functions to be curried as well.
 *
 * @param callable $fn The function to curry.
 * @return callable A curried function equivalent to $fn.
 *
 * @example
 * $add = curry(fn($a, $b, $c) => $a + $b + $c);
 * $sum = $add(1, 2)(3); // 5
 */
function curry(callable $fn): callable {
	$reflection = new ReflectionFunction($fn);
	$paramsCount = $reflection->getNumberOfRequiredParameters();

	$_curry = function(...$args) use($fn, $paramsCount, &$_curry) {
		return (count($args) >= $paramsCount)
			? $fn(...$args)
			: fn(...$rest) => $_curry(...$args, ...$rest);
    };

    return $_curry;
}


/**
 * Returns a curried version of the given function with a
 * specified minimum number of required parameters.
 *
 * Allows partial application of the function's arguments.
 *
 * @param int $paramsCount The number of required parameters for the function.
 * @param callable $fn The function to curry.
 * @return callable A curried function equivalent to $fn.
 *
 * @example
 * $add = curryN(3, fn($a, ...$rest) => $a + array_sum($rest));
 * $sum = $add(1)(2, 3); // 6
 */
function curryN(int $paramsCount, callable $fn): callable {
	$_curry = function(...$args) use ($fn, $paramsCount, &$_curry) {
		return (count($args) >= $paramsCount)
			? $fn(...$args)
			: fn(...$rest) => $_curry(...$args, ...$rest);
    };

    return $_curry;
}
