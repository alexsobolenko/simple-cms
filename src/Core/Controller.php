<?php

declare(strict_types=1);

namespace App\Core;

use App\Exception\AppException;

abstract class Controller
{
    /**
     * @param string $name
     * @param array $params
     * @return string
     * @throws AppException
     */
    protected function render(string $name, array $params = []): string
    {
        $class = get_class($this);
        $view = View::build($class, $name, $params);

        return strval($view);
    }
}
