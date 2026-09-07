<?php

use Framework\Router;
use PHPUnit\Framework\TestCase;
use App\Config\Routes;

class RouterTest extends TestCase {

    private Router $router;

    private array $test_routes = [
        ['/listings/{job_id}/edit',  '/listings/123/edit',   ['job_id' => '123']     ],
        ['/listings/{job_id}',       '/listings/456',        ['job_id' => '456']     ],
        ['/listings/{job_id}',       '/profile/123',         null                    ],
        ['/listings/{id}/edit',      '/listings/23/view',    null                    ],
        ['/listings/{id}/edit',      '/listings/23',         null                    ],
        ['/listings/{id}/edit',      '/listings/23',         null                    ],
        ['/error/{status_code}',     '/error/404',           ['status_code' => '404']],
        ['/error/{status_code}',     '/error/500',           ['status_code' => '500']],
        ['/error/{status_code}',     '/error',               null                    ],
        ['/listings/create',         '/listings/create',     []                      ],
        ['/listings/create',         '/listings/create/123', null                    ],
    ];

    protected function setUp(): void {
        $this->router = Routes::register(new Router());
    }

    public function testGetMatchReturnsParamsForMatchingRoute(): void {
        foreach ($this->test_routes as $test_case) {
            [$route_uri, $request_uri, $expected_params] = $test_case;

            $match = $this->router->routeMatch($route_uri, $request_uri);
            $actual_params = $match ? $match['params'] : null;

            $this->assertEquals(
                $expected_params,
                $actual_params,
                "Failed asserting that route '{$route_uri}' matches URI '{$request_uri}'"
            );
        }
    }

}