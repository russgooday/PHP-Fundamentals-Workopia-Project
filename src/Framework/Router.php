<?php
namespace Framework;
use PATHS;

class Router {
    protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];

    /**
     * Register a route
     *
     * @param string $method
     * @param string $uri
     * @param string $controller
     * @return void
     */
    public function registerRoute(string $method, string $uri, string $controller) {
        $this->routes[$method][] = [
            'uri' => $uri,
            'controller' => $controller
        ];
    }

    /**
     * Add a GET route
     *
     * @param string $uri
     * @param string $controller
     * @return self
     */
    public function get(string $uri, string $controller) {
        $this->registerRoute('GET', $uri, $controller);
        return $this;
    }

    /**
     * Add a POST route
     *
     * @param string $uri
     * @param string $controller
     * @return self
     */
    public function post(string $uri, string $controller) {
        $this->registerRoute('POST', $uri, $controller);
        return $this;
    }

    /**
     * Add a PUT route
     *
     * @param string $uri
     * @param string $controller
     * @return self
     */
    public function put(string $uri, string $controller) {
        $this->registerRoute('PUT', $uri, $controller);
        return $this;
    }

    /**
     * Add a DELETE route
     *
     * @param string $uri
     * @param string $controller
     * @return self
    */
    public function delete(string $uri, string $controller) {
        $this->registerRoute('DELETE', $uri, $controller);
        return $this;
    }

    public function route(string $uri, string $method, array $params = []) {
        $method = strtoupper($method);

        foreach($this->routes[$method] as $route) {
            if ($match = $this->routeMatch($route['uri'], $uri)) {
                extract($match['params']);
                include PATHS::CONTROLLERS . $route['controller'];
                return;
            }
        }

        $status_code = 404;
        include PATHS::CONTROLLERS . '/error.php';

        exit;
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
     * Last one wins for duplicate parameter names.
     *
     * @example
     * routeMatch('/listings/:id', 'http://somewhere.com/listings/23') ->
     * ['params' => ['id' => '23']]
     */
    private function routeMatch(string $route, string $uri): ?array {
        extract($this->buildRegexFrom($route));
        $path = $this->normalisePath(parse_url($uri, PHP_URL_PATH));

        if (preg_match($regex, $path, $m)) {
            // stored parameter names are combined with their respective captured values.
            return ['params' => array_combine($param_names, array_slice($m, 1))];
        }
        return null;
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
    private function buildRegexFrom(string $route, string $delim = '#'): array {
        $param_names = [];
        $reg_parts = [];

        foreach ($this->splitPathToParts($route) as $reg_part) {
            if (str_starts_with($reg_part, ':')) {
                // due to the risk of duplicate parameter names,
                // we cannot use named capture groups.
                // Instead, we will store and return them.
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
     * Split a path into parts,
     *
     * @param string $path The path to split, e.g. '/listings/123/edit'.
     * @return array
     *
     * @example
     * /folder/subfolder/page -> ['folder', 'subfolder', 'page']
     */
    private function splitPathToParts(string $path): array {
        return explode('/', trim($path, '/'));
    }

    /**
     * Normalises a path by ensuring it starts with a single leading slash.
     *
     * @param string $path The path to normalise.
     * @return string A path that always begins with '/'.
     *
     * @example
     * /folder/subfolder/page/ -> /folder/subfolder/page
     */
    private function normalisePath(string $path): string {
        return '/' . trim($path, '/');
    }

}
