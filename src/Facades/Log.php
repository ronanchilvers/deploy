<?php

namespace App\Facades;

use Psr\Log\LoggerInterface;
use Ronanchilvers\Foundation\Facade\Facade;

/**
 * Session facade class
 *
 * @method alert(string $message, array $context = array())
 * @method critical(string $message, array $context = array())
 * @method debug(string $message, array $context = array())
 * @method emergency(string $message, array $context = array())
 * @method error(string $message, array $context = array())
 * @method info(string $message, array $context = array())
 * @method log(mixed $level, string $message, array $context = array())
 * @method notice(string $message, array $context = array())
 * @method warning(string $message, array $context = array())
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Log extends Facade
{
    /**
     * @var string
     */
    protected static $serviceName = LoggerInterface::class;
}
