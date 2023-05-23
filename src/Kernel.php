<?php

declare(strict_types=1);

namespace App;

use App\Core\Connection\Database;
use App\Core\Http\Request;
use App\Core\Router\CommandLine;
use App\Core\Router\Router;
use App\Exception\BaseException;

final class Kernel
{
    public const VIEW_PATH = __DIR__ . '/View';
    public const CONTEXT_WEB = '_context.web_';
    public const CONTEXT_CLI = '_context.cli_';

    /** @var Router */
    private static Router $router;

    /** @var CommandLine */
    private static CommandLine $commandLine;

    /** @var Database */
    private static Database $database;

    /** @var string */
    private static string $context;

    /** @var array */
    private static array $get;

    /** @var array */
    private static array $post;

    /** @var array */
    private static array $files;

    /** @var array */
    private static array $server;

    /** @var array */
    private static array $env;

    /** @var array */
    private static array $session;

    /** @var array */
    private static array $arguments;

    /** @var array */
    private static array $options;

    /**
     * @param string $context
     * @param array $get
     * @param array $post
     * @param array $files
     * @param array $server
     * @param array $env
     * @param array $session
     * @param array $arguments
     * @param array $options
     * @throws BaseException
     */
    public function __construct(
        string $context = self::CONTEXT_WEB,
        array $get = [],
        array $post = [],
        array $files = [],
        array $server = [],
        array $env = [],
        array $session = [],
        array $arguments = [],
        array $options = []
    ) {
        self::$context = $context;
        self::$get = $get;
        self::$post = $post;
        self::$files = $files;
        self::$server = $server;
        self::$env = $env;
        self::$session = $session;
        self::$arguments = $arguments;
        self::$options = $options;

        self::$router = new Router();
        self::$router->registerControllerRouteAttributes(
            $this->getAllClasses(__DIR__ . '/Controller/')
        );

        self::$commandLine = new CommandLine();
        self::$commandLine->registerCommandAttributes(
            $this->getAllClasses(__DIR__ . '/Command/')
        );

        $databaseConfig = self::parseConfig('database');
        self::$database = Database::getInstance($databaseConfig);
    }

    /**
     * @param string $path
     * @return array
     */
    private function getAllClasses(string $path): array
    {
        $result = [];
        foreach (scandir($path) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            if (is_dir($path . $item)) {
                $result = array_merge($result, $this->getAllClasses($path . $item . '/'));
            } elseif (is_file($path . $item)) {
                $result[] = str_replace([__DIR__, '/', '.php'], ['\\App', '\\', ''], $path . $item);
            }
        }

        return $result;
    }

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
     * @return Database
     */
    public static function db(): Database
    {
        return self::$database;
    }

    /**
     * @param string|null $name
     * @return string
     * @throws BaseException
     */
    public function run(?string $name = null): string
    {
        if (self::$context === self::CONTEXT_CLI) {
            try {
                $result = self::$commandLine->resolve(
                    $name,
                    self::$arguments,
                    self::$options
                );
            } catch (\Throwable $e) {
                $result = $e->getMessage();
            }
        } else {
            try {
                $result = self::$router->resolve(
                    self::$server['REQUEST_URI'],
                    self::$server['REQUEST_METHOD']
                );
            } catch (\Throwable $e) {
                self::setException($e);
                $result = self::$router->resolve('/error', 'GET');
            }
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
     * @param string $path
     * @return array
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
