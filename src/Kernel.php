<?php

declare(strict_types=1);

namespace App;

use App\Core\Router;
use App\Exception\RouteNotFoundException;

final class Kernel
{
    /**
     * @var Router|null
     */
    private static ?Router $router;

    /**
     * @var array
     */
    private static array $routes = [];

    public const VIEW_PATH = __DIR__ . '/View';


    public static function dd(): void
    {
        echo '<pre>';

        $data = func_get_args();
        foreach ($data as $item) {
            var_dump($item);
        }

        echo '</pre>';
        die();
    }

    /**
     * @param array $routes
     * @return string
     */
    public static function run(array $routes): string
    {
        $routes[] = ['get', '/error', ['Error', 'index']];

        try {
            session_start();
            self::$routes = $routes;
            self::$router = new Router();
            $result = self::handleUri();
        } catch (\Throwable $e) {
            Kernel::setException($e);
            $result = self::handleException($e);
        }

        return $result;
    }

    /**
     * @param \Throwable $e
     */
    public static function setException(\Throwable $e): void
    {
        $_SESSION['_throwable'] = $e;
    }

    /**
     * @return \Throwable|null
     */
    public static function getException(): ?\Throwable
    {
        if (!isset($_SESSION['_throwable'])) {
            return null;
        }

        $exception = $_SESSION['_throwable'];
        unset($_SESSION['_throwable']);

        return $exception;
    }

    /**
     * @return string
     * @throws RouteNotFoundException
     */
    private static function handleUri(): string
    {
        foreach (self::$routes as $route) {
            $method = $route[0];
            $path = $route[1];
            $callable = $route[2];
            self::$router->$method($path, $callable);
        }

        return self::$router->resolve($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
    }

    /**
     * @param \Throwable $e
     * @return string
     * @throws RouteNotFoundException
     */
    private static function handleException(\Throwable $e)
    {
        return self::$router->resolve('/error', 'GET');
    }
}
