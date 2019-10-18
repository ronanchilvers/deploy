<?php

namespace App\Facades;

use Ronanchilvers\Foundation\Facade\Facade;

/**
 * Session facade class
 *
 * @method static set(string $key, mixed $value)
 * @method static get(string $key, mixed $default = null)
 * @method static delete(string $key)
 * @method static has(string $key)
 * @method static flash(string $message, string $type = 'info')
 * @method static getFlash(string $type)
 * @method static getFlashes()
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Session extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = 'session';
}
