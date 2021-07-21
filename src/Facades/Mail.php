<?php

namespace App\Facades;

use App\Mail\Helper;
use Ronanchilvers\Foundation\Facade\Facade;

/**
 * Session facade class
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Mail extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = Helper::class;
}
