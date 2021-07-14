<?php

namespace App\Controller\Users;

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
 * Controller for user invitations
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class InvitationController
{
    /**
     * Invite a user
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function create(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $args
    ) {
        $user = new User();
        if ('POST' == $request->getMethod()) {
            $data = $request->getParsedBody()['user'];
            $user->fromArray($data);
            if ($user->saveWithValidation('invitation')) {
                Session::flash([
                    'heading' => 'Invitation created'
                ]);
                return $response->withRedirect(
                    Router::pathFor('users.index')
                );
            }
        }

        return View::render(
            $response,
            'users/invitations/create.html.twig',
            [
                'user' => $user,
            ]
        );
    }

    /**
     * Action to accept an invitation
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function accept(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $args
    ) {
        if (!isset($args['hash']) || 0 == strlen($args['hash'])) {
            return $response->withRedirect(
                Router::pathFor('user.login')
            );
        }
        $hash = filter_var($args['hash'], FILTER_SANITIZE_STRING);
        $user = Orm::finder(User::class)->forHash($args['hash']);
        if (!$user instanceof User) {
            return $response->withRedirect(
                Router::pathFor('user.login')
            );
        }
        if ('POST' == $request->getMethod()) {
            $data     = $request->getParsedBody()['user'];
            $password = $request->getParsedBody()['password'];
            $user->fromArray($data);
            $user->setPassword(
                $password['value'],
                $password['confirm'],
            );
            $user->activate();
            if ($user->validate() && $user->save()) {
                Session::flash([
                    'heading' => 'Saved'
                ]);
                return $response->withRedirect(
                    Router::pathFor('users.index')
                );
            }
        }

        return View::render(
            $response,
            'users/invitations/accept.html.twig',
            [
                'user' => $user,
            ]
        );
    }
}
