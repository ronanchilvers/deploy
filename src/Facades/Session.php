<?php

namespace App\Facades;

use Ronanchilvers\Foundation\Facade\Facade;

/**
 * Session facade class
 *
 * @method static void set(string $key, mixed $value)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static void delete(string $key)
 * @method static bool has(string $key)
 * @method static void flash(string $message, string $type = 'info')
 * @method static mixed getFlash(string $type)
 * @method static array getFlashes()
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Session extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = 'session';
}
