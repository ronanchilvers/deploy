<?php

namespace App\Facades;

use Ronanchilvers\Foundation\Facade\Facade;
use Slim\Views\Twig as TwigView;

/**
 * Router facade class
 *
 * @method static map(array $methods, string $pattern, callable $handler)
 * @method static dispatch(\Psr\Http\Message\ServerRequestInterface $request)
 * @method static pushGroup(string $pattern, callable $callable)
 * @method static popGroup()
 * @method static getNamedRoute(string $name)
 * @method static lookupRoute(string $identifier)
 * @method static relativePathFor(string $name, array $data = [], array $queryParams = [])
 * @method static pathFor(string $name, array $data = [], array $queryParams = [])
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Router extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = 'router';
}
