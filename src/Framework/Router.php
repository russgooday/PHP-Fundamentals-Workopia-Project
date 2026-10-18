<?php

namespace Framework;

use Framework\Container\Container;

class Router {
    private const PARAM_REGEX = '#^\{([^:]+?)(?::(.*))?\}$#';

    private array $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];


    public function __construct(
        private Container $container
    ){}


    /**
     * Register a route
     * @param string $method
     * @param string $uri
     * @param string $controller
     * @return Route A Route instance
     */
    public function registerRoute(
        string $method,
        string $uri,
        string $controller
    ): Route {
        $route = new Route($method, $uri, $controller);

        // add the route to the internal routes array
        $this->routes[$method][] = $route;

        $route->setContainer($this->container);

        return $route;
    }


    /**
     * Add a GET route
     * @param string $uri
     * @param string $controller
     * @return Route
     */
    public function get(string $uri, string $controller): Route {
        return $this->registerRoute('GET', $uri, $controller);
    }


    /**
     * Add a POST route
     * @param string $uri
     * @param string $controller
     * @return Route
     */
    public function post(string $uri, string $controller): Route {
        return $this->registerRoute('POST', $uri, $controller);
    }


    /**
     * Add a PUT route
     * @param string $uri
     * @param string $controller
     * @return Route
     */
    public function put(string $uri, string $controller): Route {
        return $this->registerRoute('PUT', $uri, $controller);
    }


    /**
     * Add a DELETE route
     * @param string $uri
     * @param string $controller
     * @return Route
     */
    public function delete(string $uri, string $controller): Route {
        return $this->registerRoute('DELETE', $uri, $controller);
    }


    /**
     * Checks if the router has any routes registered for the given HTTP method.
     * @param string $method The HTTP method to check (e.g. 'GET', 'POST').
     * @return bool Returns true if there are routes for the method, false otherwise.
     */
    public function hasMethod(string $method): bool {
        return array_key_exists(strtoupper($method), $this->routes);
    }


    /**
     * Matches a URI against registered routes for the given HTTP method.
     * @param string $uri The request URI to match against registered routes.
     * @param string $method The HTTP method (e.g. 'GET', 'POST').
     * @return ?Route Returns a Route instance if a match is found, or null if no match is found.
     */
    public function match(string $uri, string $method = 'GET'): ?Route {
        $method = strtoupper($method);

        foreach ($this->routes[$method] as $route) {
            if ($matched = $this->routeMatch($route->uri, $uri)) {

                $route->setParams($matched['params']);

                return $route;
            }
        }

        return null;
    }


    /**
     * Checks whether a URI matches a route template and if a match is found
     * returns named parameter values.
     * @param string $route The route template, e.g. '/items/:id'.
     * @param string $uri The full URI or path to test.
     * @return array{params:array<string,string>}|null
     * @example routeMatch('/listings/{id}', '.../listings/23') -> ['params'=>['id'=>'23']]
     * TODO: Cleanup this messy implementation.
     */
    public function routeMatch(string $route, string $uri): ?array {
        $path = parse_url($uri, PHP_URL_PATH);

        if (!is_string($path)) {
            return null;
        }

        $uri = $this->_normalisePath($path);

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
            if (preg_match(self::PARAM_REGEX, $route_part, $matches)) {
                $name = $matches[1];
                $regex = $matches[2] ?? null;

                if ($regex && !preg_match("#^$regex$#", $uri_part)) {
                    return null;
                }

                $params[$name] = $uri_part;
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