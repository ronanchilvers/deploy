<?php

namespace App\Traits;

/**
 * Trait for things that have options
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
trait OptionableTrait
{
    /**
     * @var array
     */
    private $defaults = [];

    /**
     * @var array
     */
    private $options = [];

    /**
     * Set the default options
     *
     * @param array $defaults
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function setDefaults($defaults)
    {
        $this->defaults = $defaults;
    }

    /**
     * Set the options
     *
     * @param array $options
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Set a specific option
     *
     * @param string $key
     * @param mixed $value
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function setOption(string $key, $value)
    {
        $this->options[$key] = $value;
    }

    /**
     * Get an option
     *
     * @param string $key
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getOption($key)
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
        if (isset($this->defaults[$key])) {
            return $this->defaults[$key];
        }

        return null;
    }
}
