<?php

declare(strict_types=1);

namespace App;

use App\Core\Connection\Database;
use App\Core\Http\Request;
use App\Core\Router\Router;
use App\Exception\BaseException;

/**
 * Application kernel
 */
final class Kernel
{
    public const VIEW_PATH = __DIR__ . '/View';

    /**
     * @var Router
     */
    private static Router $router;

    /**
     * @var Database
     */
    private static Database $database;

    /**
     * @var array
     */
    private static array $get;

    /**
     * @var array
     */
    private static array $post;

    /**
     * @var array
     */
    private static array $files;

    /**
     * @var array
     */
    private static array $server;

    /**
     * @var array
     */
    private static array $env;

    /**
     * @var array
     */
    private static array $session;

    /**
     * @param array $get
     * @param array $post
     * @param array $files
     * @param array $server
     * @param array $env
     * @param array $session
     *
     * @throws BaseException
     */
    public function __construct(
        array $get,
        array $post,
        array $files,
        array $server,
        array $env,
        array $session
    ) {
        self::$get = $get;
        self::$post = $post;
        self::$files = $files;
        self::$server = $server;
        self::$env = $env;
        self::$session = $session;

        self::$router = new Router();
        self::$router->get('kernel.error', '/error', ['Error', 'index']);
        self::$router->registerControllerRouteAttributes(
            $this->getAllControllers(__DIR__ . '/Controller/')
        );

        $databaseConfig = self::parseConfig('database');
        self::$database = Database::getInstance($databaseConfig);
    }

    /**
     * Get all controllers on path
     *
     * @return array
     */
    private function getAllControllers(string $path): array
    {
        $result = [];
        foreach (scandir($path) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            if (is_file($path . $item)) {
                $result[] = str_replace([__DIR__, '/', '.php'], ['\\App', '\\', ''], $path . $item);
            } elseif (is_dir($path . $item)) {
                $result = array_merge($result, $this->getAllControllers($path . $item . '/'));
            }
        }

        return $result;
    }

    /**
     * Dump data for debug
     */
    public static function dump(): void
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
     * Get database connection instance
     *
     * @return Database
     */
    public static function db(): Database
    {
        return self::$database;
    }

    /**
     * Handle URL or show error
     *
     * @return string
     *
     * @throws BaseException
     */
    public function run(): string
    {
        try {
            $result = self::$router->resolve(
                self::$server['REQUEST_URI'],
                self::$server['REQUEST_METHOD']
            );
        } catch (\Throwable $e) {
            self::setException($e);
            $result = self::$router->resolve('/error', 'GET');
        }

        return $result;
    }

    /**
     * Save exception to session
     *
     * @param \Throwable $e
     */
    public static function setException(\Throwable $e): void
    {
        $_SESSION['_throwable'] = $e;
    }

    /**
     * Get exception from session
     *
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
     * Get request instance
     *
     * @return Request
     */
    public static function request(): Request
    {
        return Request::getInstance(
            self::$get,
            self::$post,
            self::$server,
            self::$files,
            self::$env,
            self::$session,
        );
    }

    /**
     * Parse config
     *
     * @param string $path
     *
     * @return array
     *
     * @throws BaseException
     */
    private static function parseConfig(string $name): array
    {
        try {
            $result = [];
            $data = \Spyc::YAMLLoad(__DIR__ . '/../config/' . $name . '.yaml');
            foreach ($data as $key => $value) {
                $type = null;
                if (is_string($value)) {
                    preg_match_all('/\%env:?(int|bool|array)?\(([A-Z0-9_]+)\)\%/', $value, $matches);
                    $type = $matches[1][0];
                    if (!empty($matches[0])) {
                        $value = self::$env[$matches[2][0]] ?? $matches[2][0];
                    }
                }
                $result[$key] = match ($type) {
                    'int' => intval($value),
                    'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                    'array' => json_decode($value, true),
                    default => $value,
                };
            }

            return $result;
        } catch (\Throwable $e) {
            throw new BaseException($e->getMessage(), 400);
        }
    }
}
