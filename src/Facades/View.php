<?php

namespace App\Facades;

use Ronanchilvers\Foundation\Facade\Facade;
use Slim\Views\Twig as TwigView;

/**
 * Session facade class
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class View extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = TwigView::class;
}
