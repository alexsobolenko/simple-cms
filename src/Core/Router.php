<?php

declare(strict_types=1);

namespace App\Core;

use App\Exception\RouteNotFoundException;

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
     * @return false|mixed
     * @throws RouteNotFoundException
     */
    public function resolve(string $uri, string $method)
    {
        $method = mb_strtoupper($method);
        $route = explode('?', $uri)[0];
        $action = $this->routes[$method][$route] ?? null;

        if ($action) {
            if (is_callable($action)) {
                return call_user_func($action);
            }

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
        }

        throw new RouteNotFoundException('Route not found for: [' . $method . '] ' . $route);
    }
}
