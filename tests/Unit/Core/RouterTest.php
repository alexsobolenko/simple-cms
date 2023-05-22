<?php

declare(strict_types=1);

namespace Test\Unit\Core;

use App\Core\Router\Router;
use App\Exception\BaseException;
use App\Exception\RouteNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @group core
 * @group router
 * @group core.router
 */
class RouterTest extends TestCase
{
    /** @var Router */
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
        $this->router->get('user.index', '/users', ['Users', 'get']);
        $this->router->post('user.create', '/users', ['Users', 'post']);
        $this->router->put('user.edit', '/users', ['Users', 'put']);
        $this->router->patch('user.modify', '/users', ['Users', 'patch']);
        $this->router->delete('user.delete', '/users', ['Users', 'delete']);
        $expected = [
            [
                'name' => 'user.index',
                'method' => 'get',
                'route' => '/users',
                'action' => ['Users', 'get'],
            ],
            [
                'name' => 'user.create',
                'method' => 'post',
                'route' => '/users',
                'action' => ['Users', 'post'],
            ],
            [
                'name' => 'user.edit',
                'method' => 'put',
                'route' => '/users',
                'action' => ['Users', 'put'],
            ],
            [
                'name' => 'user.modify',
                'method' => 'patch',
                'route' => '/users',
                'action' => ['Users', 'patch'],
            ],
            [
                'name' => 'user.delete',
                'method' => 'delete',
                'route' => '/users',
                'action' => ['Users', 'delete'],
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
        $this->router->get('user.index', '/users', ['Users', 'index']);
        $expected = [
            [
                'name' => 'user.index',
                'method' => 'get',
                'route' => '/users',
                'action' => ['Users', 'index'],
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
        $this->router->post('user.index', '/users', ['Users', 'index']);
        $expected = [
            [
                'name' => 'user.index',
                'method' => 'post',
                'route' => '/users',
                'action' => ['Users', 'index'],
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
        $this->router->put('user.index', '/users', ['Users', 'index']);
        $expected = [
            [
                'name' => 'user.index',
                'method' => 'put',
                'route' => '/users',
                'action' => ['Users', 'index'],
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
        $this->router->patch('user.index', '/users', ['Users', 'index']);
        $expected = [
            [
                'name' => 'user.index',
                'method' => 'patch',
                'route' => '/users',
                'action' => ['Users', 'index'],
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
        $this->router->delete('user.index', '/users', ['Users', 'index']);
        $expected = [
            [
                'name' => 'user.index',
                'method' => 'delete',
                'route' => '/users',
                'action' => ['Users', 'index'],
            ],
        ];
        $this->assertEquals($expected, $this->router->routes());
    }

    /**
     * @param string $uri
     * @param string $method
     * @test
     * @dataProvider \Test\DataProvider\RouterDataProvider::routesNotFoundCases
     * @throws BaseException
     */
    public function it_route_not_found_exception(string $uri, string $method): void
    {
        $user = new class()
        {
            public function delete(): bool
            {
                return true;
            }
        };

        $this->router->get('user.index', '/users', [$user::class, 'get']);
        $this->router->post('user.index', '/users', [$user::class, 'post']);
        $this->expectException(RouteNotFoundException::class);
        $this->router->resolve($uri, $method);
    }
}
