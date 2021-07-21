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

    /**
     * Toggle a user's level
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function toggleLevel(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $args
    ) {
        try {
            $id = filter_var($args['id'], FILTER_VALIDATE_INT, ['options' => ['default' => null ]]);
            if (is_null($id)) {
                throw new Exception('Invalid user id');
            }
            $user = Orm::finder(User::class)->one($id);
            if (! $user instanceof User) {
                throw new Exception('Unknown user');
            }
            if (Security::isCurrent($user)) {
                throw new Exception("Can't change your own level");
            }
            $user->toggleLevel();
            if (!$user->save()) {
                throw new Exception('Unable to update user level');
            }
            Session::flash(
                [
                    'heading' => "User updated",
                ],
                'info'
            );
            return $response->withRedirect(
                Router::pathFor('users.index')
            );

        } catch (Exception $ex) {
            Session::flash(
                [
                    'heading' => $ex->getMessage(),
                ],
                'error'
            );
            return $response->withRedirect(
                Router::pathFor('users.index')
            );
        }
    }

    /**
     * Toggle a user's status
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function toggleStatus(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $args
    ) {
        try {
            $id = filter_var($args['id'], FILTER_VALIDATE_INT, ['options' => ['default' => null ]]);
            if (is_null($id)) {
                throw new Exception('Invalid user id');
            }
            $user = Orm::finder(User::class)->one($id);
            if (! $user instanceof User) {
                throw new Exception('Unknown user');
            }
            if (Security::isCurrent($user)) {
                throw new Exception("Can't change your own status");
            }
            if ($user->isActive() || $user->isInvited()) {
                $user->deactivate();
            } else {
                $user->activate();
            }
            if (!$user->save()) {
                throw new Exception('Unable to update user status');
            }
            Session::flash(
                [
                    'heading' => "User updated",
                ],
                'info'
            );
            return $response->withRedirect(
                Router::pathFor('users.index')
            );

        } catch (Exception $ex) {
            Session::flash(
                [
                    'heading' => $ex->getMessage(),
                ],
                'error'
            );
            return $response->withRedirect(
                Router::pathFor('users.index')
            );
        }
    }
}
