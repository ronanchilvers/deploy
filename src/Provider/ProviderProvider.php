<?php

namespace App\Provider;

use App\Facades\Settings;
use App\Provider\Github;
use App\Provider\Gitlab;
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
        // Github
        // $container->share(Github::class, function($c) {
        //     $token = Settings::get('providers.github.token');

        //     return new Github($token);
        // });
        // Gitlab
        // $container->share(Gitlab::class, function($c) {
        //     $token = Settings::get('providers.gitlab.token');

        //     return new Gitlab($token);
        // });

        $container->share(Factory::class, function($c) {
            $factory = new Factory();
            if ($token = Settings::get('providers.github.token', false)) {
                $factory->addProvider(new Github($token));
            }
            if ($token = Settings::get('providers.gitlab.token', false)) {
                $factory->addProvider(new Gitlab($token));
            }

            return $factory;
        });
    }
}
