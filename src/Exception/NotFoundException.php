<?php

declare(strict_types=1);

namespace App\Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends AppException implements NotFoundExceptionInterface
{
    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message, $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
