<?php

namespace App\Provider;

use App\Provider\StrategyInterface;
use RuntimeException;

/**
 * Factory for version control strategies
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class StrategyFactory
{
    /**
     * @var array
     */
    protected $strategies;

    /**
     * Register a strategy
     *
     * @param string $key
     * @param App\Provider\StrategyInterface $strategy
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function registerStrategy(string $key, StrategyInterface $strategy)
    {
        $this->strategies[$key] = $strategy;
    }

    /**
     * Factory method to get a provider for a given
     *
     * @param string $key
     * @return App\Provider\StrategyInterface
     * @throws RuntimeException If asked for an invalid strategy
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function get($key)
    {
        if (isset($this->strategies[$key])) {
            return $this->strategies[$key];
        }

        throw new RuntimeException(
            sprintf('Invalid provider strategy %s - do you need to register it?', $key)
        );
    }
}
