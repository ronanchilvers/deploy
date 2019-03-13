<?php

namespace App\Facades;

use Ronanchilvers\Foundation\Facade\Facade;
use Slim\Views\Twig as TwigView;

/**
 * Router facade class
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Router extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = 'router';
}
