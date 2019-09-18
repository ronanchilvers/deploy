<?php

namespace App\Action;

use App\Action\Context;
use ReflectionClass;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Foundation\Traits\Optionable;
use Ronanchilvers\Utility\Str;

/**
 * Action to symlink the deployment in to the live location
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
abstract class AbstractAction
{
    use Optionable;

    /**
     * @see App\Action\ActionInterface::getName()
     */
    public function getName()
    {
        $reflection = new ReflectionClass($this);
        return Str::snake(
            str_replace(
                'Action',
                '',
                $reflection->getShortName()
            )
        );
    }

    /**
     * @see App\Action\ActionInterface::run()
     */
    abstract public function run(Config $configuration, Context $context);
}
