<?php

declare(strict_types=1);

namespace App\Core\Controller;

use App\Core\Render\View;
use App\Exception\BaseException;

/**
 * Default abstract controller class
 */
abstract class AbstractController
{
    /**
     * Render view
     *
     * @param string $name
     * @param array $params
     *
     * @return string
     *
     * @throws BaseException
     */
    protected function render(string $name, array $params = []): string
    {
        $class = get_class($this);
        $view = View::build($class, $name, $params);

        return strval($view);
    }

    /**
     * Redirect to url
     *
     * @param string $url
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
    }
}
