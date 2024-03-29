<?php

declare(strict_types=1);

namespace App\Exception;

use App\Core\Http\Response;

class BaseException extends \Exception
{
    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message,
        $code = Response::HTTP_BAD_REQUEST,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
