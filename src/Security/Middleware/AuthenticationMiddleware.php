<?php

namespace App\Security\Middleware;

use App\Facades\Router;
use App\Facades\Security;
use App\Facades\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ronanchilvers\Foundation\Traits\Optionable;

/**
 * Authentication middleware responsible for managing access to protected routes
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class AuthenticationMiddleware
{
    use Optionable;

    /**
     * @var array
     */
    protected $anonymousRoutes = [];

    /**
     * Class constructor
     *
     * @param array $anonymouseRoutes
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct($options = [])
    {
        $this->setDefaults([
            'anonymous_routes' => [
                'user.login',
            ],
            'login_route'    => 'user.login',
            'store_redirect' => true,
        ]);
        $this->setOptions($options);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        $anonymousRoutes = $this->getOption('anonymous_routes', []);
        if (!in_array($request->getAttribute('route')->getName(), $anonymousRoutes)) {
            if (!Security::hasLogin()) {
                return $response->withRedirect(
                    Router::pathFor($this->getOption('login_route'))
                );
            }
        }

        return $next($request, $response);
    }
}
