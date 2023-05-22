<?php

declare(strict_types=1);

namespace App\Attribute\ORM;

use App\Exception\BaseException;
use App\Util\DateTimeUtils;

#[\Attribute]
class Column
{
    /**
     * @param string $name
     * @param string $type
     * @param int $length
     * @param string|null $order
     * @param string|null $generate
     */
    public function __construct(
        public string $name,
        public string $type,
        public int $length = 0,
        public ?string $order = null,
        public ?string $generate = null
    ) {}

    /**
     * @param $value
     * @return mixed
     * @throws BaseException
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
     * @throws BaseException
     */
    public function model(array $data): mixed
    {
        return match($this->type) {
            'integer' => intval($data[$this->name]),
            'datetime' => DateTimeUtils::fromString($data[$this->name]),
            default => $data[$this->name],
        };
    }
}
