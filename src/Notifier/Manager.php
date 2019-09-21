<?php

namespace App\Notifier;

use App\Facades\Log;
use Ronanchilvers\Foundation\Config;

/**
 * Notifier manager class
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Manager
{
    /**
     * @var array
     */
    protected $adaptors = [];

    /**
     * Register a notifier with the manager
     *
     * @param App\Notifier\AdaptorInterface
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function registerAdaptor(AdaptorInterface $adaptor)
    {
        $this->adaptors[get_class($adaptor)] = $adaptor;
    }

    /**
     * Send a notification through all adaptors
     *
     * @param App\Notifier\Notification $notification
     * @param array $options An array of options for all notifiers, keyed by notifier key
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function sendNotification(Notification $notification, array $options)
    {
        Log::debug(sprintf('Sending notification to %d adaptors', count($this->adaptors)), [
            'message' => $notification->getMessage(),
        ]);
        foreach ($this->adaptors as $adaptor) {
            Log::debug(sprintf('Using %s adaptor', get_class($adaptor)));
            $key = $adaptor->getKey();
            if (!isset($options[$key])) {
                continue;
            }
            $adaptorOptions = $options[$key];
            $adaptor->send($notification, $adaptorOptions);
        }
    }

    /**
     * Send a string message
     *
     * @param string $message
     * @param array $options An array of options for all notifiers, keyed by notifier key
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function send($message, array $options)
    {
        $notification = new Notification($message);
        $this->sendNotification($notification, $options);
    }
}
