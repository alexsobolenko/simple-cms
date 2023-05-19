<?php

declare(strict_types=1);

namespace App\Attribute\ORM;

use App\Exception\BaseException;
use App\Util\DateTimeUtils;

/**
 * ORM column attribute
 */
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
     * Prepare property value for DB
     *
     * @param $value
     *
     * @return mixed
     *
     * @throws BaseException
     */
    public function value($value): mixed
    {
        return match($this) {
            'varchar' => '"' . strval($value) . '"',
            'integer' => intval($value),
            'datetime' => '"' . DateTimeUtils::forDatabase($value) . '"',
            default => $value,
        };
    }

    /**
     * Prepare propery value
     *
     * @param array $data
     *
     * @return mixed
     *
     * @throws BaseException
     */
    public function model(array $data): mixed
    {
        return match($this) {
            'integer' => intval($data[$this->name]),
            'datetime' => DateTimeUtils::fromString($data[$this->name]),
            default => $data[$this->name],
        };
    }
}
