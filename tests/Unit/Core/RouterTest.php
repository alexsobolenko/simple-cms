<?php

declare(strict_types=1);

namespace Test\Unit\Core;

use App\Core\Router;
use App\Exception\AppException;
use App\Exception\RouteNotFoundException;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    /**
     * @var Router
     */
    private Router $router;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = new Router();
    }

    /**
     * @test
     */
    public function it_routes_is_clear(): void
    {
        $this->assertEmpty($this->router->routes());
    }

    /**
     * @test
     */
    public function it_registers_a_routes(): void
    {
        $this->router = new Router();
        $this->router->get('/users', ['Users', 'get']);
        $this->router->post('/users', ['Users', 'post']);
        $this->router->put('/users', ['Users', 'put']);
        $this->router->patch('/users', ['Users', 'patch']);
        $this->router->delete('/users', ['Users', 'delete']);
        $expected = [
            'GET' => [
                '/users' => ['Users', 'get'],
            ],
            'POST' => [
                '/users' => ['Users', 'post'],
            ],
            'PUT' => [
                '/users' => ['Users', 'put'],
            ],
            'PATCH' => [
                '/users' => ['Users', 'patch'],
            ],
            'DELETE' => [
                '/users' => ['Users', 'delete'],
            ],
        ];
        $this->assertEquals($expected, $this->router->routes());
    }

    /**
     * @test
     */
    public function it_registers_a_get_route(): void
    {
        $this->router = new Router();
        $this->router->get('/users', ['Users', 'index']);
        $expected = [
            'GET' => [
                '/users' => ['Users', 'index'],
            ],
        ];
        $this->assertEquals($expected, $this->router->routes());
    }

    /**
     * @test
     */
    public function it_registers_a_post_route(): void
    {
        $this->router = new Router();
        $this->router->post('/users', ['Users', 'index']);
        $expected = [
            'POST' => [
                '/users' => ['Users', 'index'],
            ],
        ];
        $this->assertEquals($expected, $this->router->routes());
    }

    /**
     * @test
     */
    public function it_registers_a_put_route(): void
    {
        $this->router = new Router();
        $this->router->put('/users', ['Users', 'index']);
        $expected = [
            'PUT' => [
                '/users' => ['Users', 'index'],
            ],
        ];
        $this->assertEquals($expected, $this->router->routes());
    }
    /**
     * @test
     */
    public function it_registers_a_patch_route(): void
    {
        $this->router = new Router();
        $this->router->patch('/users', ['Users', 'index']);
        $expected = [
            'PATCH' => [
                '/users' => ['Users', 'index'],
            ],
        ];
        $this->assertEquals($expected, $this->router->routes());
    }

    /**
     * @test
     */
    public function it_registers_a_delete_route(): void
    {
        $this->router = new Router();
        $this->router->delete('/users', ['Users', 'index']);
        $expected = [
            'DELETE' => [
                '/users' => ['Users', 'index'],
            ],
        ];
        $this->assertEquals($expected, $this->router->routes());
    }

    /**
     * @param string $uri
     * @param string $method
     * @test
     * @dataProvider \Test\DataProvider\RouterDataProvider::routesNotFoundCases
     * @throws AppException
     */
    public function it_route_not_found_exception(string $uri, string $method): void
    {
        $user = new class() {
            public function delete(): bool {
                return true;
            }
        };

        $this->router->get('/users', [$user::class, 'get']);
        $this->router->post('/users', [$user::class, 'post']);
        $this->expectException(RouteNotFoundException::class);
        $this->router->resolve($uri, $method);
    }
}
