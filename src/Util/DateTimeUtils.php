<?php

declare(strict_types=1);

namespace App\Util;

use App\Exception\AppException;

final class DateTimeUtils
{
    /**
     * @return \DateTimeImmutable
     * @throws AppException
     */
    public static function now(): \DateTimeImmutable
    {
        return self::fromString('now');
    }

    /**
     * @param string $value
     * @return \DateTimeImmutable
     * @throws AppException
     */
    public static function fromString(string $value): \DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable($value);
        } catch (\Throwable $e) {
            throw new AppException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param mixed $value
     * @return string|null
     * @throws AppException
     */
    public static function forDatabase(mixed $value): ?string
    {
        if (is_string($value)) {
            $value = self::fromString($value);
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        return null;
    }
}
