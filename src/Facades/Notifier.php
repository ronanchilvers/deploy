<?php

namespace App\Facades;

use App\Notifier\Manager;
use Ronanchilvers\Foundation\Facade\Facade;

/**
 * Notification manager facade class
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Notifier extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = Manager::class;
}
