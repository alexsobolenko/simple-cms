<?php

declare(strict_types=1);

namespace App\Core\Render;

final class ViewParams
{
    /** @var array */
    private array $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (!is_string($key)
                || str_starts_with($key, '_')
                || (is_string($value)
                    && (str_starts_with($value, './') || str_starts_with($value, '../'))
                )
            ) {
                continue;
            }

            $this->data[$key] = $value;
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }
}
