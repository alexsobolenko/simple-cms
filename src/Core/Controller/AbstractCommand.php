<?php

declare(strict_types=1);

namespace App\Core\Controller;

abstract class AbstractCommand
{
    public const EXIT_OK = 0;
    public const EXIT_ERROR = 1;

    /**
     * @param array $arguments
     * @param array $options
     * @return int
     */
    abstract public function run(array $arguments = [], array $options = []): int;
}
