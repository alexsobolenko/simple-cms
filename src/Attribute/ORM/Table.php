<?php

declare(strict_types=1);

namespace App\Attribute\ORM;

#[\Attribute]
class Table
{
    /**
     * @param string $name
     */
    public function __construct(
        public string $name
    ) {}
}
