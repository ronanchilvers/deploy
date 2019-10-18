<?php

namespace App\Console;

use App\Facades\Settings;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Registry;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Ronanchilvers\Container\Container;
use Ronanchilvers\Container\ServiceProviderInterface;

/**
 * App service provider
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Provider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function register(Container $container)
    {
        // Logger
        $container->extend(LoggerInterface::class, function($logger) {
            $logger->pushHandler(
                new StreamHandler(
                    'php://stdout',
                    Logger::DEBUG
                )
            );

            return $logger;
        });
    }
}
