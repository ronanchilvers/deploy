<?php

namespace App\Provider;

use App\Facades\Settings;
use App\Provider\Github;
use Ronanchilvers\Container\Container;
use Ronanchilvers\Container\ServiceProviderInterface;

/**
 * Provider for ... err... providers!
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ProviderProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function register(Container $container)
    {
        // Gitlab
        $container->share(Github::class, function ($c){
            $token = Settings::get('providers.github.token');

            return new Github($token);
        });

        $container->share(Factory::class, function ($c) {
            $factory = new Factory();
            $factory->addProvider($c->get(Github::class));

            return $factory;
        });
    }
}
