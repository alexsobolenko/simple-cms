<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Controller;
use App\Exception\ViewBuildException;
use App\Kernel;

class ErrorController extends Controller
{
    /**
     * @return string
     * @throws ViewBuildException
     */
    public function indexAction(): string
    {
        $exception = Kernel::getException();
        $code = $exception?->getCode();

        return $this->render('index', [
            'title' => 'Error',
            'message' => $exception?->getMessage() ?? 'Fatal error',
            'code' => intval($code) === 0 ? 500 : $code,
        ]);
    }
}
