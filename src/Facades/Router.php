<?php

namespace App\Facades;

use Ronanchilvers\Foundation\Facade\Facade;
use Slim\Views\Twig as TwigView;

/**
 * Router facade class
 *
 * @method static \Slim\Interfaces\RouteInterface map(array $methods, string $pattern, callable $handler)
 * @method static array dispatch(\Psr\Http\Message\ServerRequestInterface $request)
 * @method static \Slim\Interfaces\RouteGroupInterface pushGroup(string $pattern, callable $callable)
 * @method static array popGroup()
 * @method static \Slim\Interfaces\RouteInterface getNamedRoute(string $name)
 * @method static \Slim\Interfaces\RouteInterface lookupRoute(string $identifier)
 * @method static string relativePathFor(string $name, array $data = [], array $queryParams = [])
 * @method static string pathFor(string $name, array $data = [], array $queryParams = [])
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Router extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = 'router';
}
