<?php

namespace App\Facades;

use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Foundation\Facade\Facade;
use Slim\Views\Twig as TwigView;

/**
 * Settings facade class
 *
 * @method @method set(string $key, mixed $value)
 * @method has(string $key)
 * @method get(string $key, mixed $default = null)
 * @method getOrThrow(string $key, string $message = null)
 * @method getAll()
 * @method merge(\Ronanchilvers\Foundation\Config $config)
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Settings extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = 'settings';

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public static function getService()
    {
        $service = parent::getService();

        return new Config($service);
    }
}
