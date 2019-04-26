<?php

namespace App\Provider;

use App\Traits\OptionableTrait;

/**
 * Abstract base class for all provider classes
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
abstract class AbstractProvider
{
    use OptionableTrait;

    /**
     * Check if a repository exists by name
     *
     * The repository name is something like 'vendor/repo'
     *
     * @param string $name
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    abstract public function exists($name);
}
