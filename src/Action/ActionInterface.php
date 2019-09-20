<?php

namespace App\Action;

use App\Action\Context;
use Ronanchilvers\Foundation\Config;

/**
 * Interface for build actions
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
interface ActionInterface
{
    /**
     * Get the name for this action
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getName();

    /**
     * Run this action
     *
     * @param Context
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function run(Config $configuration, Context $context);
}