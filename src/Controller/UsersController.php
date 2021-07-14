<?php

namespace App\Controller;

use App\Facades\Router;
use App\Facades\Security;
use App\Facades\Session;
use App\Facades\View;
use App\Model\User;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ronanchilvers\Orm\Orm;
use RuntimeException;

/**
 * Controller for administering users
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class UsersController
{
    /**
     * Login action for users
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function index(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {
        $users = Orm::finder(User::class)->all();

        return View::render(
            $response,
            'users/index.html.twig',
            [
                'users' => $users,
            ]
        );
    }
}
