<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Route;
use App\Core\Controller\AbstractController;
use App\Kernel;

class ErrorController extends AbstractController
{
    #[Route(name: 'kernel.error', path: '/error')]
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
