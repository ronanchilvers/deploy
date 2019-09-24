<?php

namespace App\Facades;

use App\Security\Manager;
use Ronanchilvers\Foundation\Facade\Facade;

/**
 * Security manager facade class
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Security extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = Manager::class;
}
