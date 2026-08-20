<?php
/**
 * Get the base path
 *
 * @param string $path
 * @return string
 */
function base_path(string $path = ''): string {
    if (!$path) {
        return __DIR__;
    }

    $sep = DIRECTORY_SEPARATOR;

    return __DIR__
        . $sep
        . ltrim(str_replace(['\\', '/'], $sep, $path), $sep);
}