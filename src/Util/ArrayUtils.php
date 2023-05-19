<?php

declare(strict_types=1);

namespace App\Util;

/**
 * Utils for arrays
 */
final class ArrayUtils
{

    /**
     * Compare all items with specified
     *
     * @param array $data
     * @param mixed $compareValue
     * @param bool $strict
     *
     * @return bool
     */
    public static function every(array $data, mixed $compareValue = true, bool $strict = false): bool
    {
        if ($strict) {
            if (empty($data)) {
                return false;
            }

            foreach ($data as $item) {
                if ($item !== $compareValue) {
                    return false;
                }
            }
        } else {
            foreach ($data as $item) {
                if ($item != $compareValue) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Count all items in array
     * = value not null - count all value in array
     *
     * @param array $data
     * @param mixed $value
     *
     * @return int
     */
    public static function count(array $data, mixed $value = null): int
    {
        if ($value === null) {
            return count($data);
        }

        $filtered = array_filter($data, static fn($item): bool => $item === $value);

        return count($filtered);
    }

    /**
     * Get array values from enum values
     *
     * @param array $items
     *
     * @return array
     */
    public static function enumValues(array $items): array
    {
        return array_map(static fn($item) => $item->value, $items);
    }

    /**
     * Fitler array
     *
     * @param array $data
     * @param callable $filter
     *
     * @return array
     */
    public static function filter(array $data, callable $filter): array
    {
        $filtered = array_filter($data, $filter);

        return array_values($filtered);
    }

    /**
     * Filter array not empty values
     *
     * @param array $data
     *
     * @return array
     */
    public static function filterNotEmptyValues(array $data): array
    {
        return self::filter($data, static fn($item) => !empty($item));
    }
}
