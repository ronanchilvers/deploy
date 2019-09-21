<?php

namespace App\Notifier;

/**
 * Interface for notification adaptors
 *
 * Notification adaptors are responsible for sending a notification to a
 * specific service.
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
interface AdaptorInterface
{
    /**
     * Get the key for this adaptor
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getKey();

    /**
     * Send a notification
     *
     * @param App\Notifier\Notification
     * @param array $options
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function send(Notification $notification, array $options = []);
}
