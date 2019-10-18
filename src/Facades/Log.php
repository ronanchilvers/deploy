<?php

namespace App\Facades;

use Psr\Log\LoggerInterface;
use Ronanchilvers\Foundation\Facade\Facade;

/**
 * Session facade class
 *
 * @method static void alert(string $message, array $context = array())
 * @method static void critical(string $message, array $context = array())
 * @method static void debug(string $message, array $context = array())
 * @method static void emergency(string $message, array $context = array())
 * @method static void error(string $message, array $context = array())
 * @method static void info(string $message, array $context = array())
 * @method static void log(mixed $level, string $message, array $context = array())
 * @method static void notice(string $message, array $context = array())
 * @method static void warning(string $message, array $context = array())
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Log extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = LoggerInterface::class;
}
