<?php

namespace App\Facades;

use Ronanchilvers\Foundation\Facade\Facade;

/**
 * Session facade class
 *
 * @method set(string $key, mixed $value)
 * @method get(string $key, mixed $default = null)
 * @method delete(string $key)
 * @method has(string $key)
 * @method flash(string $message, string $type = 'info')
 * @method getFlash(string $type)
 * @method getFlashes()
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Session extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = 'session';
}
