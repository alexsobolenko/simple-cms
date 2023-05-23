<?php

declare(strict_types=1);

namespace App\Attribute\Command;

#[\Attribute]
class Command
{
    /**
     * @param string $name
     * @param string $description
     */
    public function __construct(
        public string $name,
        public string $description
    ) {}
}
