<?php

namespace App\Attribute\ORM;

use App\Exception\AppException;
use App\Util\DateTimeUtils;

#[\Attribute]
class Column
{
    /**
     * @param string $name
     * @param string $type
     * @param int $length
     * @param string|null $order
     */
    public function __construct(
        public string $name,
        public string $type,
        public int $length = 0,
        public ?string $order = null
    ) {}

    /**
     * @param $value
     * @return mixed
     * @throws AppException
     */
    public function value($value): mixed
    {
        return match($this->type) {
            'varchar' => '"' . strval($value) . '"',
            'integer' => intval($value),
            'datetime' => '"' . DateTimeUtils::forDatabase($value) . '"',
            default => $value,
        };
    }

    /**
     * @param array $data
     * @return mixed
     * @throws AppException
     */
    public function model(array $data): mixed
    {
        $value = $data[$this->name];

        return match($this->type) {
            'integer' => intval($value),
            'datetime' => DateTimeUtils::fromString($value),
            default => $value,
        };
    }
}
