<?php
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
            if (
                $match =
                    $this->matchParts(
                        $this->splitPathToParts($route['uri']),
                        $this->splitPathToParts($uri)
                    )
            ){
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
     * matchParts
     * Match route URI parts against request path parts and extract dynamic parameters.
     *
     * Route parts prefixed with ":" are treated as named parameters.
     * Returns null when the path does not match the route.
     *
     * @param array<int, string> $route_parts Route definition parts (e.g. ['listings', ':id']).
     * @param array<int, string> $path_parts Request path parts (e.g. ['listings', '123']).
     * @return array{path: array<int, string>, params: array<string, string>}|null
     */
    private function matchParts(array $route_parts, array $path_parts): ?array {
        $params = [];

        // return early if the number of parts don't match
        if (count($route_parts) !== count($path_parts))
            return null;

        foreach ($route_parts as $i => $route_part) {
            $path_part = $path_parts[$i];

            // if we have a parameter in the route, store the path part value
            // in the params array with the parameter name as the key
            if (str_starts_with($route_part, ':'))
                $params[substr($route_part, 1)] = $path_part;

            elseif ($path_part !== $route_part)
                return null;
        }

        return ['path' => $path_parts, 'params' => $params];
    }

    /**
     * Split a URI's path into parts,
     *
     * @param string $uri
     * @return array
     *
     * @example
     * http://somewhere.com/folder/subfolder/page -> ['folder', 'subfolder', 'page']
     */
    private function splitPathToParts(string $uri): array {
        return explode('/', ltrim(parse_url($uri, PHP_URL_PATH), '/'));
    }

}
