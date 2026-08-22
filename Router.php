<?php
class Router {
    protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];

    protected $errorCodes = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error'
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

    /**
     * Load error page
     *
     * @param string $method
     * @param string $uri
     * @param int $httpCode
     * @return void
     */
    public function error($method, $uri, $httpCode = 404) {
        $errorMessage = $this->errorCodes[$httpCode] ?? 'Unknown Error';

        http_response_code($httpCode);
        logError("{$httpCode} {$errorMessage}: For {$method} {$uri}");
        loadView("error/{$httpCode}", [ 'error_code' => $httpCode ]);
        exit;
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

        $this->error($method, $uri, 404);
    }

}
