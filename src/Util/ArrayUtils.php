<?php

declare(strict_types=1);

namespace App\Util;

final class ArrayUtils
{

    /**
     * @param array $data
     * @param mixed $compareValue
     * @param bool $strict
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
     * @param array $data
     * @param mixed $value
     * @return int
     */
    public static function count(array $data, mixed $value = null): int
    {
        if ($value !== null) {
            $data = self::filter($data, static fn($item): bool => $item === $value);
        }

        return count($data);
    }

    /**
     * @param array $data
     * @param callable $callback
     * @return array
     */
    public static function filter(array $data, callable $callback): array
    {
        $filtered = array_filter($data, $callback);

        return array_values($filtered);
    }

    /**
     * @param array $data
     * @return array
     */
    public static function filterNotEmptyValues(array $data): array
    {
        return self::filter($data, static fn($item) => !empty($item));
    }

    /**
     * @param array $data
     * @param callable $callback
     * @return array
     */
    public static function map(array $data, callable $callback): array
    {
        return array_map($callback, $data);
    }

    /**
     * @param array $data
     * @return string
     */
    public static function printTable(array $data): string
    {
        $firstItem = array_shift($data);
        $length = count($firstItem) - 1;
        foreach ($firstItem as $item) {
            $length += strlen($item);
        }
        $lines = [
            implode('|', $firstItem),
            str_pad('', $length, '-'),
            ...self::map($data, static fn($item) => implode('|', $item)),
        ];

        return implode(PHP_EOL, $lines);
    }
}
