<?php

namespace App\Provider;

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
        // Gitlab
        $container->share(Gitlab::class, function ($c){
            $provider = new Gitlab([
                'api_uri' => Settings::get('provider.gitlab.endpoint', 'https://gitlab.com/api/v4/'),
                'token'   => Settings::get('provider.gitlab.token', false)
            ]);

            return $provider;

            // $client = \Gitlab\Client::create(
            //     Settings::get('provider.gitlab.endpoint', 'https://gitlab.com')
            // );
            // $client->authenticate(
            //     Settings::get('provider.gitlab.token'),
            //     \Gitlab\Client::AUTH_HTTP_TOKEN
            // );

            // return $client;
        });
    }
}
