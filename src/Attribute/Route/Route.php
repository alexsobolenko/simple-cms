<?php

declare(strict_types=1);

namespace App\Attribute\Route;

#[\Attribute]
class Route
{
    /**
     * @param string $name
     * @param string $path
     * @param string|string[] $method
     */
    public function __construct(
        public string $name,
        public string $path,
        public string|array $method = 'GET'
    ) {}
}
