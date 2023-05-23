<?php

declare(strict_types=1);

namespace App\Attribute\Command;

use App\Util\DateTimeUtils;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class Argument
{
    /**
     * @param string $name
     * @param bool $required
     * @param mixed $default
     * @param string|null $type
     */
    public function __construct(
        public string $name,
        public bool $required = false,
        public mixed $default = null,
        public ?string $type = null,
    ) {}

    /**
     * @param mixed $value
     * @return mixed
     */
    public function value(mixed $value): mixed
    {
        return match ($this->type) {
            'string' => (string) $value,
            'integer' => (int) $value,
            'float' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'datetime' => DateTimeUtils::fromString($value),
            default => $value,
        };
    }
}
