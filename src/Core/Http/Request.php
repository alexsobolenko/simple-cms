<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Exception\BaseException;

/**
 * @property-read array $query
 * @property-read array $request
 * @property-read array $env
 * @property-read array $files
 * @property-read array $session
 */
final class Request
{
    /**
     * @var Request|null
     */
    private static ?Request $instance = null;

    /**
     * @param array $query
     * @param array $request
     * @param array $server
     * @param array $files
     * @param array $env
     * @param array $session
     */
    private function __construct(
        private array $query,
        private array $request,
        private array $server,
        private array $files,
        private array $env,
        private array $session
    ) {}

    /**
     * @param array $query
     * @param array $request
     * @param array $server
     * @param array $files
     * @param array $env
     * @param array $session
     * @return Request
     */
    public static function getInstance(
        array $query,
        array $request,
        array $server,
        array $files,
        array $env,
        array $session
    ): Request {
        if (self::$instance === null) {
            self::$instance = new self($query, $request, $server, $files, $env, $session);
        }

        return self::$instance;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws BaseException
     */
    public function __get(string $name)
    {
        if (!in_array($name, ['query', 'request', 'files', 'env', 'session'])) {
            throw new BaseException('Unknown request data', Response::HTTP_BAD_REQUEST);
        }

        return $this->$name;
    }
}
