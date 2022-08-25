<?php

declare(strict_types=1);

namespace App\Attribute;

#[\Attribute]
class Route
{
    /**
     * @param string $path
     * @param string $method
     */
    public function __construct(
        public string $path,
        public string $method = 'get'
    ) {}
}
