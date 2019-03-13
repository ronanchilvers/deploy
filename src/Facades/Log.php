<?php

namespace App\Facades;

use Psr\Log\LoggerInterface;
use Ronanchilvers\Foundation\Facade\Facade;

/**
 * Session facade class
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Log extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = LoggerInterface::class;
}
