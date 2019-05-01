<?php

namespace App\Facades;

use App\Provider\StrategyFactory;
use Ronanchilvers\Foundation\Facade\Facade;
use Slim\Views\Twig as TwigView;

/**
 * Provider strategy facade class
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Strategy extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = StrategyFactory::class;
}
