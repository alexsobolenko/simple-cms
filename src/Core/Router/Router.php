<?php

declare(strict_types=1);

namespace App\Core\Router;

use App\Attribute\Route;
use App\Exception\BaseException;
use App\Exception\RouteNotFoundException;
use App\Util\ArrayUtils;

/**
 * Router
 */
final class Router
{
    /**
     * @var array
     */
    private array $routes = [];

    /**
     * Register route
     *
     * @param string|string[] $method
     * @param string $route
     * @param callable|array $action
     *
     * @return $this
     */
    public function register(
        string $name,
        string|array $method,
        string $route,
        callable|array $action
    ): self {
        $allowedMethods = is_string($method) ? [$method] : $method;
        foreach ($allowedMethods as $allowedMethod) {
            $this->routes[] = [
                'action' => $action,
                'method' => $allowedMethod,
                'name' => $name,
                'route' => $route,
            ];
        }

        return $this;
    }

    /**
     * Register route. Method GET
     *
     * @param string $name
     * @param string $route
     * @param callable|array $action
     *
     * @return self
     */
    public function get(string $name, string $route, callable|array $action): self
    {
        return $this->register($name, 'get', $route, $action);
    }

    /**
     * Register route. Method POST
     *
     * @param string $name
     * @param string $route
     * @param callable|array $action
     *
     * @return self
     */
    public function post(string $name, string $route, callable|array $action): self
    {
        return $this->register($name, 'post', $route, $action);
    }

    /**
     * Register route. Method PUT
     *
     * @param string $name
     * @param string $route
     * @param callable|array $action
     *
     * @return self
     */
    public function put(string $name, string $route, callable|array $action): self
    {
        return $this->register($name, 'put', $route, $action);
    }

    /**
     * Register route. Method PATCH
     *
     * @param string $name
     * @param string $route
     * @param callable|array $action
     *
     * @return self
     */
    public function patch(string $name, string $route, callable|array $action): self
    {
        return $this->register($name, 'patch', $route, $action);
    }

    /**
     * Register route. Method DELETE
     *
     * @param string $name
     * @param string $route
     * @param callable|array $action
     *
     * @return self
     */
    public function delete(string $name, string $route, callable|array $action): self
    {
        return $this->register($name, 'delete', $route, $action);
    }

    /**
     * Get registered routes
     *
     * @param string|null $method
     * @param string|null $route
     * @param string|null $name
     *
     * @return array
     *
     */
    public function routes(
        ?string $method = null,
        ?string $route = null,
        ?string $name = null
    ): array {
        $routes = $this->routes;
        if ($method !== null) {
            $routes = ArrayUtils::filter($routes, fn($r): bool => $r['method'] === $method);
        }
        if ($route !== null) {
            $routes = ArrayUtils::filter($routes, fn($r): bool => $r['route'] === $route);
        }
        if ($name !== null) {
            $routes = ArrayUtils::filter($routes, fn($r): bool => $r['name'] === $name);
        }

        return $routes;
    }

    /**
     * Resolve request URI to route
     *
     * @param string $uri
     * @param string $method
     *
     * @return mixed
     *
     * @throws BaseException
     */
    public function resolve(string $uri, string $method): mixed
    {
        $method = mb_strtoupper($method);
        $route = explode('?', $uri)[0];
        $action = ($this->routes($method, $route)[0] ?? [])['action'] ?? null;
        if (!is_array($action)) {
            throw new RouteNotFoundException("Route not found for: [{$method}] {$route}");
        }

        [$controllerClass, $methodAction] = $action;
        if (!class_exists($controllerClass)) {
            throw new RouteNotFoundException("Controller not found '{$controllerClass}'. Route: {$route}");
        }

        $controller = new $controllerClass();
        if (!method_exists($controller, $methodAction)) {
            throw new RouteNotFoundException("Action '{$methodAction}' not found in controller '{$controllerClass}'. Route: {$route}");
        }

        return call_user_func_array([$controller, $methodAction], []);
    }

    /**
     * Register routes with `Route` attribute on controller methods
     *
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
                        $this->register(
                            $route->name,
                            $route->method,
                            $route->path,
                            [$controller, $method->getName()]
                        );
                    }
                }
            } catch (\Throwable) {}
        }
    }
}
