<?php

declare(strict_types=1);

namespace App\Util;

use App\Exception\Util\DateTimeUtilsException;

/**
 * Utils for date
 * = Default time zone - Europe/Moscow
 * = Use env var `TIME_ZONE` to change time zone
 */
final class DateTimeUtils
{
    /**
     * Get default time zone
     *
     * @return string
     */
    public static function defaultTimeZone(): string
    {
        return $_ENV['TIME_ZONE'] ?? 'Europe/Moscow';
    }

    /**
     * Get date now
     *
     * @return \DateTimeImmutable
     *
     * @throws DateTimeUtilsException
     */
    public static function now(): \DateTimeImmutable
    {
        return self::fromString('now');
    }

    /**
     * Get date from string
     *
     * @param string $value
     *
     * @return \DateTimeImmutable
     *
     * @throws DateTimeUtilsException
     */
    public static function fromString(string $value): \DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable($value, new \DateTimeZone(self::defaultTimeZone()));
        } catch (\Throwable $e) {
            throw new DateTimeUtilsException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get date from timestamp
     *
     * @param int $value
     *
     * @return \DateTimeImmutable
     *
     * @throws DateTimeUtilsException
     */
    public static function fromTimestamp(int $value): \DateTimeImmutable
    {
        return self::now()->setTimestamp($value);
    }

    /**
     * Get date from params
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @param int $microsecond
     *
     * @return \DateTimeImmutable
     *
     * @throws DateTimeUtilsException
     */
    public static function fromParams(
        int $year = 2023,
        int $month = 1,
        int $day = 1,
        int $hour = 0,
        int $minute = 0,
        int $second = 0,
        int $microsecond = 0
    ): \DateTimeImmutable {
        $date = self::now();
        $date->setDate($year, $month, $day);
        $date->setTime($hour, $minute, $second, $microsecond);

        return $date;
    }

    /**
     * Format date string
     *
     * @param string $value
     * @param string $format
     *
     * @return string
     *
     * @throws DateTimeUtilsException
     */
    public static function format(string $value, string $format = \DateTimeInterface::ATOM): string
    {
        $date = self::fromString($value);

        return $date->format($format);
    }

    /**
     * Format date for database (ATOM)
     *
     * @param mixed $value
     *
     * @return string|null
     *
     * @throws DateTimeUtilsException
     */
    public static function forDatabase(mixed $value): ?string
    {
        $date = match (true) {
            is_string($value) => self::fromString($value),
            is_integer($value) => self::fromTimestamp($value),
            default => $value,
        };

        if (!$date instanceof \DateTimeInterface) {
            return null;
        }

        return $date->format(\DateTimeInterface::ATOM);
    }
}
