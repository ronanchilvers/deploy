<?php

namespace App\Provider;

use App\Facades\Settings;
use App\Provider\Github;
use App\Provider\Gitlab;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
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
                $client = new Client([
                    'headers' => [
                        "Authorization" => "token {$token}",
                    ]
                ]);
                $factory->addProvider(new Github($client, $token));
            }

            if ($token = Settings::get('providers.gitlab.token', false)) {
                $client = new Client([
                    'headers' => [
                        "Private-Token" => $token,
                    ]
                ]);
                $factory->addProvider(new Gitlab($client, $token));
            }

            return $factory;
        });
    }
}
