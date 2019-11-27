<?php

namespace App\Provider;

use App\Facades\Settings;
use App\Provider\Bitbucket;
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

            if ($token = Settings::get('providers.bitbucket.token', false)) {
                $username = Settings::get('providers.bitbucket.username', false);
                $client = new Client([
                    'auth' => [ $username, $token ],
                ]);
                $factory->addProvider(new Bitbucket($client, $token));
            }

            return $factory;
        });
    }
}
