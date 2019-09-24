<?php

namespace App\Security;

use App\Facades\Log;
use App\Security\Manager;
use Ronanchilvers\Container\Container;
use Ronanchilvers\Container\ServiceProviderInterface;

/**
 * Provider for security services
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class SecurityProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function register(Container $container)
    {
        $container->share(Manager::class, function ($c) {
            return new Manager($c->get('session'));
        });
    }
}
