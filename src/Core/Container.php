<?php

namespace App\Core;

use App\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

final class Container implements ContainerInterface
{
    /**
     * @var array
     */
    private array $entries = [];

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->entries[$id]);
    }

    /**
     * @param string $id
     * @return mixed
     * @throws NotFoundException
     */
    public function get(string $id)
    {
        if (!$this->has($id)) {
            throw new NotFoundException('Class "' . $id . '" has no bindings');
        }

        $entry = $this->entries[$id];

        return $entry($this);
    }

    /**
     * @param string $id
     * @param callable $concrete
     */
    public function set(string $id, callable $concrete): void
    {
        $this->entries[$id] = $concrete;
    }
}
