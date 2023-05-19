<?php

declare(strict_types=1);

namespace App\Exception;

use App\Core\Http\Response;

class ViewBuildException extends BaseException
{
    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message = 'View not found',
        $code = Response::HTTP_NOT_FOUND,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
