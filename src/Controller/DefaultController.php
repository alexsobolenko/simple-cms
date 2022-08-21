<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Controller;
use App\Exception\AppException;

class DefaultController extends Controller
{
    /**
     * @return string
     * @throws AppException
     */
    public function indexAction(): string
    {
        return $this->render('index', [
            'title' => 'Home',
            'message' => 'This is home page',
        ]);
    }
}
