<?php

namespace App\Action;

use App\Action\Context;
use Ronanchilvers\Foundation\Config;

/**
 * Interface for build actions
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
interface HookableInterface
{
    /**
     * Run a set of hooks from a configuration object
     *
     * @param Ronanchilvers\Foundation\Config $configuration
     * @param App\Action\Context
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function runHooks(Config $configuration, Context $context);
}
