<?php
// ?P<param>
/**
function buildRegex(string $route): string {
    $parts = explode('/', trim($route, '/'));

    $regexParts = array_map(function (string $part) {
        if (str_starts_with($part, ':')) {
            return '(?P<' . substr($part, 1) . '>[^/]+)';
        }
        return preg_quote($part, '#');
    }, $parts);

    return '#^/' . implode('/', $regexParts) . '$#';
}
*/
// function buildRegex(string $route): string {
//     return preg_replace(["#:[^\/]+#", "#\/#"],["([^/]+)", "\/"],$route);
// }


// function buildRegex(string $route): string {
//     $replacer = fn($m) => $m[1] ? "(?P<$m[1]>[^\/]+)" : "\/";
//     $regex = "#:([^\/]+)|(\/)#";
//     $route_rx = preg_replace_callback($regex, $replacer, $route);

//     return '#^' . $route_rx . '$#';
// }


// function buildRegex(string $route): string {
//     $replacer = fn($m) => "(?P<$m[1]>[^\/]+)";
//     $params = "#:([^\/]+)#";
//     $route_rx = preg_replace_callback($params, $replacer, $route);

//     return '#^' . preg_quote($route_rx) . '$#';
// }

function escape(string $str): string {
    return preg_quote($str);
}

// function getMatch(string $path, string $route) {
//     $path = parse_url($path, PHP_URL_PATH);
//     $regex = buildRegex($route);

//     if (preg_match($regex, $path, $matches))
//         return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
//     return null;
// }



// function splitPathToParts(string $route): array {
//     return explode('/', trim(parse_url($route, PHP_URL_PATH), '/'));
// }

/**
 * Splits a path-like string into its URL segments.
 *
 * @param string $path The path to split, such as '/users/42' or 'users/42'.
 * @return array<int, string> The path segments without empty leading or trailing elements.
 */
function splitPathToParts(string $path): array {
    return explode('/', trim($path, '/'));
}

/**
 * Normalises a path by ensuring it starts with a single leading slash.
 *
 * @param string $path The path to normalise.
 * @return string A path that always begins with '/'.
 */
function normalisePath(string $path): string {
    return '/' . trim($path, '/');
}

/**
 * Builds a regex and parameter-name list for matching a route template.
 *
 * Route parameters are written as ':name' and are converted into a capture group
 * that matches a non-empty segment. Literal route parts are escaped before being
 * combined into the final expression.
 *
 * @param string $route The route template, e.g. '/listings/:id/edit'.
 * @param string $delim The regex delimiter to wrap the pattern in.
 * @return array{regex: string, param_names: array<int, string>}
 * The regex and the ordered parameter names.
 */
function buildRegexFrom(string $route, string $delim = '#'): array {
    $param_names = [];
    $reg_parts = [];

    foreach (splitPathToParts($route) as $reg_part) {
        if (str_starts_with($reg_part, ':')) {
            // Duplicate parameter names may occur, so named capture groups
            // are not suitable here. Store the parameter names separately
            // and associate them with the captured values afterward.
            $param_names[] = substr($reg_part, 1);
            $reg_parts[] = "([^\/]+)";
        } else {
            $reg_parts[] = preg_quote($reg_part, $delim);
        }
    }

    return [
        'regex' => $delim . "^\/" . implode('\/', $reg_parts). "$" . $delim,
        'param_names' => $param_names
    ];
}

/**
 * Checks whether a URI matches a route template and returns matched parameter values.
 *
 * The URI path is normalised and matched against a generated regex. If a match is
 * found, the captured values are mapped back to their parameter names.
 *
 * @param string $route The route template, e.g. '/items/:id'.
 * @param string $uri The full URI or path to test.
 * @return array{params: array<string, string>}|null
 * The matched parameter values, or null if no match.
 */
function routeMatch(string $route, string $uri): ?array {
    extract(buildRegexFrom($route));
    $path = normalisePath(parse_url($uri, PHP_URL_PATH));

    if (preg_match($regex, $path, $m))
        return ['params' => array_combine($param_names, array_slice($m, 1))];
    return null;
}

if ($match = routeMatch(
    '/list-ings/:id/ed.it/:type',
    '/list-ings/23/ed.it/fruit/'
)) {
    print_r($match['params']);
}

/**
Array
(
    [id] => 23
    [type] => fruit
)
*/