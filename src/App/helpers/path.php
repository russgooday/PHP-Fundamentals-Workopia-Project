<?php
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__, 3));


/**
 * Get the base path
 *
 * @param string $path
 * @return string
 */
function basePath(string $path = ''): string {
    $sep = DIRECTORY_SEPARATOR;

    return ($path)
        ? ROOT_PATH . $sep . ltrim(str_replace(['\\', '/'], $sep, $path), $sep)
        : ROOT_PATH;
}
