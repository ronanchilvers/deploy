<?php

namespace App\Action;

use App\Action\Context;
use App\Model\Finder\EventFinder;
use Ronanchilvers\Foundation\Config;

/**
 * Interface for build actions
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
interface ActionInterface
{
    /**
     * Get the key for this action
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getKey();

    /**
     * Set the event finder for this action
     *
     * @param \App\Model\Finder\EventFinder $eventFinder
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function setEventFinder(EventFinder $eventFinder);

    /**
     * Is this action hookable?
     *
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isHookable();

    /**
     * Run a set of hooks from a configuration object
     *
     * @param string $hook Either 'before' or 'after'
     * @param \Ronanchilvers\Foundation\Config $configuration
     * @param \App\Action\Context
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function runHooks($hook, Config $configuration, Context $context);

    /**
     * Run this action
     *
     * @param Context
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function run(Config $configuration, Context $context);
}
