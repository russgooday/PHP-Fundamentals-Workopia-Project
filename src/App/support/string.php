<?php

if (!function_exists('str_split_pos')) {
    function str_split_pos(string $str, int $pos) {
        return [substr($str, 0, $pos), substr($str, $pos)];
    }
}

if (!function_exists('str_split_trim')) {
    function str_split_trim(string $str): array {
        return array_map('trim', explode(',', $str));
    }
}
