<?php

/**
 * String helper functions.
 */


/**
 * Split a string into two parts at the given position.
 * @param string $str The string to split.
 * @param int $pos The position at which to split the string.
 * @return array An array containing the two parts of the string.
 */
if (!function_exists('str_split_pos')) {
    function str_split_pos(string $str, int $pos) {
        return [substr($str, 0, $pos), substr($str, $pos)];
    }
}


/**
 * Split a string into an array of trimmed values using a comma as the delimiter.
 *
 * @param string $str The string to split.
 * @return array The array of trimmed values.

 */
if (!function_exists('str_split_trim')) {
    function str_split_trim(string $str): array {
        return array_map('trim', explode(',', $str));
    }
}


/**
 * Wrapper for preg_match_all that returns only the matches array.
 *
 * @param string $regex The regular expression pattern.
 * @param string $str The string to search.
 * @param int $flags Optional flags for preg_match_all.
 * @return array The array of matches.
 */

function regex_match_all(string $regex, string $str, int $flags = 0): array {
    preg_match_all($regex, $str, $matches, $flags);
    return $matches;
}


/**
 * Wrapper for preg_match that returns only the matches array.
 *
 * @param string $regex The regular expression pattern.
 * @param string $str The string to search.
 * @param int $flags Optional flags for preg_match.
 * @return array The array of matches.
 */
function regex_match(string $regex, string $str, int $flags = 0): array {
    preg_match($regex, $str, $matches, $flags);
    return $matches;
}
