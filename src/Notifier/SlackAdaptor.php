<?php

namespace App\Notifier;

use App\Facades\Log;
use App\Notifier\AdaptorInterface;

/**
 * Notification adaptor that sends messages to a slack webhook
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class SlackAdaptor extends AbstractAdaptor implements AdaptorInterface
{
    /**
     * {@inheridoc}
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function send(Notification $notification, array $options = [])
    {
        if (!isset($options['webhook'])) {
            Log::error('Invalid notification configuration - slack webhook missing', [
                'options' => $options,
            ]);
            return false;
        }
        if (false === ($curl = curl_init($options['webhook']))) {
            Log::error('Unable to initialise slack webhook curl handle');
            return false;
        }
        $payload = json_encode([
            'text' => $notification->getMessage(),
        ]);
        curl_setopt_array($curl, [
            CURLOPT_USERAGENT      => 'ronanchilvers/deploy - curl ' . curl_version()['version'],
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => ['payload' => $payload],
        ]);
        $result = curl_exec($curl);
        $info = curl_getinfo($curl);
        if (!$result) {
            Log::error('Unable to send slack message to webhook', $info);
        } else {
            Log::debug('Sent slack message to webhook', $info);
        }
        curl_close($curl);

        return true;
    }
}
