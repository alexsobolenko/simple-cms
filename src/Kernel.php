<?php

declare(strict_types=1);

namespace App;

use App\Core\Request;
use App\Core\Router;
use App\Exception\AppException;

final class Kernel
{
    public const VIEW_PATH = __DIR__ . '/View';

    /**
     * @var Router
     */
    private Router $router;

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
     */
    public function __construct(array $get, array $post, array $files, array $server, array $env, array $session)
    {
        self::$get = $get;
        self::$post = $post;
        self::$files = $files;
        self::$server = $server;
        self::$env = $env;
        self::$session = $session;

        $this->router = new Router();
        $this->router->get('/error', ['Error', 'index']);
        $data = self::parseConfig('routes');
        foreach ($data as $name => $params) {
            $this->router->{$params['method']}(
                $params['path'],
                explode('::', $params['handler'])
            );
        }
    }

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
     * @return string
     * @throws AppException
     */
    public function run(): string
    {
        try {
            $result = $this->handleUri();
        } catch (\Throwable $e) {
            $result = $this->handleException($e);
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
     * @return string
     * @throws AppException
     */
    private function handleUri(): string
    {
        return $this->router->resolve(
            self::$server['REQUEST_URI'],
            self::$server['REQUEST_METHOD']
        );
    }

    /**
     * @param \Throwable $e
     * @return string
     * @throws AppException
     */
    private function handleException(\Throwable $e)
    {
        self::setException($e);

        return $this->router->resolve('/error', 'GET');
    }

    /**
     * @param string $path
     * @return array
     * @throws AppException
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
            throw new AppException($e->getMessage(), 400);
        }
    }
}
