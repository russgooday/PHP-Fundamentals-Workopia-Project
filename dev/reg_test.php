<?php
/**
function getErrorCode(?string $path): ?int {
    if($path && preg_match('#^/error/(\d{3})$#', $path, $matches)) {
        return (int) $matches[1];
    }
    return null;
}

if ($code = getErrorCode('/error/203')) {
    echo "Error code {$code}\n";
} else {
    echo "No error code found in the path.\n";
}
*/


function splitUriToParts(string $uri): array {
    return explode('/', ltrim(parse_url($uri, PHP_URL_PATH), '/'));
}

// print_r(splitUriToParts('http://workopia.test//listings/:id/edit?color=red&type=metal'));

function checkPathTest(string $uri): ?array {
    $path_parts = splitUriToParts($uri);
    $route_path_parts = splitUriToParts('/listings/:id/edit');
    $params = [];

    foreach ($route_path_parts as $i => $route_part) {
        $path_part = $path_parts[$i] ?? null;

        if (str_starts_with($route_part, ':'))
            $params[substr($route_part, 1)] = $path_part;

        elseif ($path_part !== $route_part)
            return null;
    }

    return ['params' => $params];
}

// var_dump(checkPathTest('http://workopia.test//listings/23/edit?color=red&type=metal'));
var_dump(checkPathTest('http://workopia.test//listings'));
