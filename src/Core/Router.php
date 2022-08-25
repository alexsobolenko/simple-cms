<?php

declare(strict_types=1);

namespace App\Core;

use App\Attribute\Route;
use App\Exception\AppException;
use App\Exception\RouteNotFoundException;
use App\Kernel;

final class Router
{
    /**
     * @var array
     */
    private array $routes = [];

    /**
     * @param string $method
     * @param string $route
     * @param callable|array $action
     * @return $this
     */
    public function register(string $method, string $route, callable|array $action): self
    {
        $this->routes[mb_strtoupper($method)][$route] = $action;

        return $this;
    }

    /**
     * @param string $route
     * @param callable|array $action
     * @return $this
     */
    public function get(string $route, callable|array $action): self
    {
        return $this->register('get', $route, $action);
    }

    /**
     * @param string $route
     * @param callable|array $action
     * @return $this
     */
    public function post(string $route, callable|array $action): self
    {
        return $this->register('post', $route, $action);
    }

    /**
     * @param string $route
     * @param callable|array $action
     * @return $this
     */
    public function put(string $route, callable|array $action): self
    {
        return $this->register('put', $route, $action);
    }

    /**
     * @param string $route
     * @param callable|array $action
     * @return $this
     */
    public function patch(string $route, callable|array $action): self
    {
        return $this->register('patch', $route, $action);
    }

    /**
     * @param string $route
     * @param callable|array $action
     * @return $this
     */
    public function delete(string $route, callable|array $action): self
    {
        return $this->register('delete', $route, $action);
    }

    /**
     * @return array
     */
    public function routes(): array
    {
        return $this->routes;
    }

    /**
     * @param string $uri
     * @param string $method
     * @return mixed
     * @throws AppException
     */
    public function resolve(string $uri, string $method): mixed
    {
        $method = mb_strtoupper($method);
        $route = explode('?', $uri)[0];
        $action = $this->routes[$method][$route] ?? null;

        if (is_array($action)) {
            [$class, $method] = $action;
            $controllerClass = "\\App\\Controller\\{$class}Controller";
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                $methodAction = "{$method}Action";
                if (method_exists($controller, $methodAction)) {
                    return call_user_func_array([$controller, $methodAction], []);
                }
            }
        }

        throw new RouteNotFoundException('Route not found for: [' . $method . '] ' . $route);
    }

    /**
     * @param array $controllers
     */
    public function registerControllerRouteAttributes(array $controllers): void
    {
        foreach ($controllers as $controller) {
            try {
                $reflectionController = new \ReflectionClass($controller);
                foreach ($reflectionController->getMethods() as $method) {
                    $attributes = $method->getAttributes(Route::class);
                    foreach ($attributes as $attribute) {
                        /** @var Route $route */
                        $route = $attribute->newInstance();
                        $controller = str_replace('\\App\\Controller\\', '', $controller);
                        $controller = str_replace('Controller', '', $controller);
                        $action = str_replace('Action', '', $method->getName());
                        $this->register($route->method, $route->path, [$controller, $action]);
                    }
                }
            } catch (\Throwable) {}
        }
    }
}
