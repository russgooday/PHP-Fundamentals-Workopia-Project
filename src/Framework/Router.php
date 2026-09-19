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
                    'params' => $matched['params']
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
    public function routeMatch(string $route, string $uri, string $rx='#^\{([^}]+)\}$#'): ?array {
        $uri = $this->_normalisePath(parse_url($uri, PHP_URL_PATH));

        $route_parts = $this->_splitPathToParts($route);
        $uri_parts = $this->_splitPathToParts($uri);
        $params = [];

        // early exit if a different number of parts
        if (count($uri_parts) !== count($route_parts)) return null;

        foreach ($route_parts as $i => $route_part) {
            $uri_part = $uri_parts[$i];

            // we have found a matching literal
            if ($route_part === $uri_part) {
                continue;
            }

            // we have found a matching parameter
            if (preg_match($rx, $route_part, $matches)) {
                $params[$matches[1]] = $uri_part;
                continue;
            }
            // not a match, so exit with false
            return null;
        }

        return ['uri' => $uri, 'params' => $params];
    }

    /**
     * Split a path into parts,
     * @param string $path The path to split, e.g. '/listings/123/edit'.
     * @return array An array of path parts, e.g. ['listings', '123', 'edit'].
     */
    private function _splitPathToParts(string $path): array {
        return explode('/', trim($path, '/'));
    }


    /**
     * Normalises a path by ensuring it starts with a single leading slash.
     * @param string $path The path to normalise.
     * @return string A path that always begins with '/'.
     */
    private function _normalisePath(string $path): string {
        return '/' . trim($path, '/');
    }
}