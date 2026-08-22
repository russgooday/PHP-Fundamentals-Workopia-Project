<?php
class Router {
    protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];

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
        $path = parse_url($uri, PHP_URL_PATH);

        foreach($this->routes[$method] as $route) {
            if ($route['uri'] === $path) {

                // GET: testing handling of query params
                if ($method === 'GET')
                    if ($query = parse_url($uri, PHP_URL_QUERY))
                        parse_str($query, $query_params);

                require basePath($route['controller']);
                return;
            }
        }

        http_response_code(404);
        logError("404 Not Found: No route found for {$method} {$uri}");
        loadView('error/404', [ 'error_type' => 404 ]);
        exit;
    }

}
