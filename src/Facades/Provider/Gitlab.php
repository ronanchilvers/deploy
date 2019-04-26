<?php

namespace App\Facades\Provider;

use Ronanchilvers\Foundation\Facade\Facade;

/**
 * Gitlab facade class
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Gitlab extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = 'provider.gitlab';
}
