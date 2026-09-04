<?php

namespace Framework;

class Router {
    protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];


    /**
     * Register a route
     * @param string $method
     * @param string $uri
     * @param string $controller
     * @return void
     */
    public function registerRoute(string $method, string $uri, string $controller): void {
        $this->routes[$method][] = [
            'uri' => $uri,
            'controller' => $controller
        ];
    }


    /**
     * Add a GET route
     * @param string $uri
     * @param string $controller
     * @return self
     */
    public function get(string $uri, string $controller): self {
        $this->registerRoute('GET', $uri, $controller);
        return $this;
    }


    /**
     * Add a POST route
     * @param string $uri
     * @param string $controller
     * @return self
     */
    public function post(string $uri, string $controller): self {
        $this->registerRoute('POST', $uri, $controller);
        return $this;
    }


    /**
     * Add a PUT route
     * @param string $uri
     * @param string $controller
     * @return self
     */
    public function put(string $uri, string $controller): self {
        $this->registerRoute('PUT', $uri, $controller);
        return $this;
    }


    /**
     * Add a DELETE route
     * @param string $uri
     * @param string $controller
     * @return self
     */
    public function delete(string $uri, string $controller): self {
        $this->registerRoute('DELETE', $uri, $controller);
        return $this;
    }

    /**
     * Matches a URI against registered routes for the given HTTP method.
     * @param string $uri The request URI to match against registered routes.
     * @param string $method The HTTP method (e.g. 'GET', 'POST').
     * @return array|false Returns an array with 'controller' and 'params' if
     * a match is found, or false if no match is found.
     */
    public function match(string $uri, string $method = 'GET'): array|false {
        $method = strtoupper($method);

        foreach ($this->routes[$method] as $route) {
            if ($matched = $this->routeMatch($route['uri'], $uri)) {
                return [
                    'controller' => $route['controller'],
                    'params' => $matched['params'],
                ];
            }
        }

        return false;
    }


    /**
     * Checks whether a URI matches a route template and if a match is found
     * returns named parameter values.
     * @param string $route The route template, e.g. '/items/:id'.
     * @param string $uri The full URI or path to test.
     * @return array{params:array<string,string>}|null
     * @example routeMatch('/listings/:id', '.../listings/23') -> ['params'=>['id'=>'23']]
     */
    function routeMatch(string $route, string $uri): ?array {
        $path = $this->normalisePath(parse_url($uri, PHP_URL_PATH));
        $regex = $this->buildRegex($route);

        if (preg_match($regex, $path, $matches)) {
            return ['params' => array_filter(
                $matches, 'is_string', ARRAY_FILTER_USE_KEY
            )];
        }
        return null;
    }


    /**
     * Builds a regex and parameter-name list.
     * @param string $route The route template, e.g. '/listings/:id/edit'.
     * @param string $delim The regex delimiter to wrap the pattern in.
     * @return string The regex pattern to match the route.
     */
    function buildRegex(string $route, string $delim = '#'): string {
        $parts = $this->splitPathToParts($route);

        $regexParts = array_map(function (string $part) use ($delim) {
            if (str_starts_with($part, ':')) {
                return '(?P<' . substr($part, 1) . '>[^/]+)';
            }
            return preg_quote($part, $delim);
        }, $parts);

        return $delim . '^/' . implode('/', $regexParts) . '$' . $delim;
    }


    /**
     * Split a path into parts,
     * @param string $path The path to split, e.g. '/listings/123/edit'.
     * @return array An array of path parts, e.g. ['listings', '123', 'edit'].
     */
    private function splitPathToParts(string $path): array {
        return explode('/', trim($path, '/'));
    }


    /**
     * Normalises a path by ensuring it starts with a single leading slash.
     * @param string $path The path to normalise.
     * @return string A path that always begins with '/'.
     */
    private function normalisePath(string $path): string {
        return '/' . trim($path, '/');
    }
}