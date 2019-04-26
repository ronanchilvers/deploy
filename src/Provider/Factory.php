<?php

namespace App\Provider;

use App\Provider\ProviderInterface;

/**
 * Factory for version control providers
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Factory
{
    /**
     * @var array
     */
    protected $providers;

    /**
     * Register a provider
     *
     * @param string $key
     * @param App\Provider\ProviderInterface $provider
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function registerProvider(string $key, ProviderInterface $provider)
    {
        $this->providers[$key] = $provider;
    }

    /**
     * Factory method to get a provider for a given
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function name($arg)
    {
        //body
    }
}
