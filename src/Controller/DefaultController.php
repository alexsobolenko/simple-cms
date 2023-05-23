<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\Route\Route;
use App\Core\Controller\AbstractController;
use App\Kernel;
use App\Model\User;

class DefaultController extends AbstractController
{
    #[Route(name: 'default.index', path: '/', method: 'get')]
    public function indexAction(): string
    {
        $users = User::findAll();

        return $this->render('index', [
            'title' => 'User form',
            'users' => $users,
        ]);
    }

    #[Route(name: 'default.post', path: '/', method: 'post')]
    public function postAction(): string
    {
        $request = Kernel::request();

        $id = $request->query['id'] ?? null;
        if ($id === null) {
            $user = new User($request->request['name']);
        } else {
            $user = User::findOne($request->query);
            $user->name = $request->request['name'];
        }
        $user->save($id !== null);

        return $this->render('post', [
            'title' => 'User details',
            'user' => $user,
        ]);
    }

    #[Route(name: 'default.info', path: '/info', method: 'get')]
    public function infoAction(): string
    {
        $request = Kernel::request();
        $user = User::findOne($request->query);

        return $this->render('info', [
            'title' => 'User edit',
            'user' => $user,
        ]);
    }

    #[Route(name: 'default.delete', path: '/delete', method: 'get')]
    public function deleteAction(): void
    {
        $request = Kernel::request();
        $user = User::findOne($request->query);
        $user->delete();
        $this->redirect('/');
    }
}
