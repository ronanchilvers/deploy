<?php

namespace App\Facades;

use Psr\Log\LoggerInterface;
use Ronanchilvers\Foundation\Facade\Facade;

/**
 * Session facade class
 *
 * @method static alert(string $message, array $context = array())
 * @method static critical(string $message, array $context = array())
 * @method static debug(string $message, array $context = array())
 * @method static emergency(string $message, array $context = array())
 * @method static error(string $message, array $context = array())
 * @method static info(string $message, array $context = array())
 * @method static log(mixed $level, string $message, array $context = array())
 * @method static notice(string $message, array $context = array())
 * @method static warning(string $message, array $context = array())
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Log extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = LoggerInterface::class;
}
