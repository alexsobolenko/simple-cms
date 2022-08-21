<?php

declare(strict_types=1);

namespace App\Core;

use App\Exception\AppException;

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
     * @var array
     */
    private array $query;

    /**
     * @var array
     */
    private array $request;

    /**
     * @var array
     */
    private array $server;

    /**
     * @var array
     */
    private array $files;

    /**
     * @var array
     */
    private array $env;

    /**
     * @var array
     */
    private array $session;

    /**
     * @param array $query
     * @param array $request
     * @param array $server
     * @param array $files
     * @param array $env
     * @param array $session
     */
    private function __construct(array $query, array $request, array $server, array $files, array $env, array $session)
    {
        $this->query = $query;
        $this->request = $request;
        $this->server = $server;
        $this->files = $files;
        $this->env = $env;
        $this->session = $session;
    }

    /**
     * @param array $query
     * @param array $request
     * @param array $server
     * @param array $files
     * @param array $env
     * @param array $session
     * @return Request
     */
    public static function getInstance(array $query, array $request, array $server, array $files, array $env, array $session): Request
    {
        if (self::$instance === null) {
            self::$instance = new self($query, $request, $server, $files, $env, $session);
        }

        return self::$instance;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws AppException
     */
    public function __get(string $name)
    {
        if (!in_array($name, ['query', 'request', 'files', 'env', 'session'])) {
            throw new AppException('Unknown request data');
        }

        return $this->$name;
    }
}
