<?php

namespace App\Facades;

use Ronanchilvers\Foundation\Facade\Facade;
use Slim\Views\Twig as TwigView;

/**
 * Router facade class
 *
 * @method @method map(array $methods, string $pattern, callable $handler)
 * @method dispatch(\Psr\Http\Message\ServerRequestInterface $request)
 * @method pushGroup(string $pattern, callable $callable)
 * @method popGroup()
 * @method getNamedRoute(string $name)
 * @method lookupRoute(string $identifier)
 * @method relativePathFor(string $name, array $data = [], array $queryParams = [])
 * @method pathFor(string $name, array $data = [], array $queryParams = [])
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Router extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = 'router';
}
