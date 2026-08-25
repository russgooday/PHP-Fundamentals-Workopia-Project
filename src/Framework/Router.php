<?php

namespace Framework;
use App\Config\Paths;

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
     * Dispatch the request to the matching route's controller.
     * @param string $uri The request URI to match against registered routes.
     * @param string $method The HTTP method (e.g. 'GET', 'POST').
     * @param array $params Optional additional parameters.
     * @return void
     */
    public function route(string $uri, string $method, array $params = []): void {
        $method = strtoupper($method);

        foreach ($this->routes[$method] as $route) {

            if ($matched = $this->routeMatch($route['uri'], $uri)) {
                extract($matched['params']);
                include Paths::CONTROLLERS . $route['controller'];
                return;
            }
        }

        $status_code = 404;
        include Paths::CONTROLLERS . '/error.php';

        exit;
    }

    /**
     * Checks whether a URI matches a route template and if a match is found
     * returns named parameter values.
     * @param string $route The route template, e.g. '/items/:id'.
     * @param string $uri The full URI or path to test.
     * @return array{params:array<string,string>}|null
     * @example routeMatch('/listings/:id', '.../listings/23') -> ['params'=>['id'=>'23']]
     */
    private function routeMatch(string $route, string $uri): ?array {
        extract($this->buildRegexFrom($route)); // $regex, $param_names
        $path = $this->normalisePath(parse_url($uri, PHP_URL_PATH));

        if (preg_match($regex, $path, $m))
            // stored parameter names are combined with their respective captured values.
            return ['params' => array_combine($param_names, array_slice($m, 1))];
        return null;
    }

    /**
     * Builds a regex and parameter-name list.
     * @param string $route The route template, e.g. '/listings/:id/edit'.
     * @param string $delim The regex delimiter to wrap the pattern in.
     * @return array{regex:string,param_names:array<int,string>}
     */
    private function buildRegexFrom(string $route, string $delim = '#'): array {
        $param_names = [];
        $reg_parts = [];

        foreach ($this->splitPathToParts($route) as $reg_part) {

            if (str_starts_with($reg_part, ':')) {
                $param_names[] = substr($reg_part, 1);
                $reg_parts[] = "([^\/]+)";
            } else {
                $reg_parts[] = preg_quote($reg_part, $delim);
            }
        }

        return [
            'regex' => $delim . "^\/" . implode('\/', $reg_parts) . "$" . $delim,
            'param_names' => $param_names
        ];
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
