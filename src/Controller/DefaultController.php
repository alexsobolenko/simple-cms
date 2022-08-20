<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Controller;
use App\Exception\ViewBuildException;

class DefaultController extends Controller
{
    /**
     * @return string
     * @throws ViewBuildException
     */
    public function indexAction(): string
    {
        return $this->render('index', [
            'title' => 'Home',
            'message' => 'This is home page',
        ]);
    }
}
