<?php

namespace App;

use ArrayAccess;
use Iterator;

/**
 * Generic collection of message arrays with keys
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class MessageCollection implements ArrayAccess, Iterator
{
    /**
     * @var array
     */
    protected $data;

    /**
     * Class constructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * Add a value to the collection
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function add(string $key, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Does this collection have a given key?
     *
     * @param string $key
     * @return boolean
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function has(string $key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Get a message array by key
     *
     * @param string $key
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function get(string $key)
    {
        if (!$this->offsetExists($key)) {
            return null;
        }
        return $this->offsetGet($key);
    }

    /**
     * Get a message array by key as a string
     *
     * @param string $key
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function str($key)
    {
        if ($messages = $this->get($key)) {
            $messages = implode(', ', $messages);
        }

        return $messages;
    }

    /** START Iterator compliance **/

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function rewind()
    {
        reset($this->data);
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function valid()
    {
        return key($this->data) !== null;
    }

    /** END Iterator compliance **/

    /** START ArrayAccess compliance **/

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function offsetGet($offset)
    {
        $value = $this->data[$offset];
        if (is_string($value)) {
            $value = [$value];
        }

        return $value;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function offsetSet($offset, $value)
    {
        $this->offset[$offset] = $value;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    /** END ArrayAccess compliance **/
}
