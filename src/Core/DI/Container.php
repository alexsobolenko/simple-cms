<?php

declare(strict_types=1);

namespace App\Core\DI;

use App\Core\Http\Response;
use App\Exception\Core\ContainerException;
use Psr\Container\ContainerInterface;

/**
 * DI container
 */
final class Container implements ContainerInterface
{
    /**
     * @var mixed[]
     */
    private array $entries = [];

    /**
     * Check for class binding exists
     *
     * @param string $id
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->entries[$id]);
    }

    /**
     * Get class binding
     *
     * @param string $id
     *
     * @return mixed
     *
     * @throws ContainerException
     */
    public function get(string $id)
    {
        if (!$this->has($id)) {
            throw new ContainerException("Class '{$id}' has no bindings", Response::HTTP_NOT_FOUND);
        }

        $entry = $this->entries[$id];

        return $entry($this);
    }

    /**
     * Bind class
     *
     * @param string $id
     *
     * @param callable $concrete
     */
    public function set(string $id, callable $concrete): void
    {
        $this->entries[$id] = $concrete;
    }
}
