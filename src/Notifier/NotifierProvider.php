<?php

namespace App\Notifier;

use App\Facades\Log;
use App\Facades\Settings;
use App\Notifier\Manager;
use App\Notifier\SlackAdaptor;
use ReflectionClass;
use ReflectionException;
use Ronanchilvers\Container\Container;
use Ronanchilvers\Container\ServiceProviderInterface;
use Ronanchilvers\Utility\Str;

/**
 * Provider for notification services
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class NotifierProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function register(Container $container)
    {
        $container->share(SlackAdaptor::class, function() {
            return new SlackAdaptor();
        });

        $container->share(Manager::class, function($c) {
            $manager = new Manager();
            $manager->registerAdaptor($c->get(SlackAdaptor::class));

            return $manager;
        });
    }
}
