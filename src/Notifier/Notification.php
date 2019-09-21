<?php

namespace App\Notifier;

/**
 * Represents a single notification
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Notification
{
    /**
     * @var string
     */
    protected $message;

    /**
     * Class constructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Get the message for this notification
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getMessage()
    {
        return $this->message;
    }
}
