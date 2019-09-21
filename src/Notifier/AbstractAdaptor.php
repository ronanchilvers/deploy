<?php

namespace App\Notifier;

use ReflectionClass;
use Ronanchilvers\Utility\Str;

/**
 * Abstract adaptor class
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
abstract class AbstractAdaptor
{
    /**
     * {@inheritdoc}
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getKey()
    {
        $reflection = new ReflectionClass($this);
        $name       = Str::snake(
            str_replace(
                'Adaptor',
                '',
                $reflection->getShortName()
            )
        );

        return $name;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function send(Notification $notification);
}
