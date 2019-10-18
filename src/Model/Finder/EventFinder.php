<?php

namespace App\Model\Finder;

use App\Model\Deployment;
use App\Model\Event;
use ClanCats\Hydrahon\Query\Expression;
use Ronanchilvers\Orm\Finder;

/**
 * Finder for deployment event models
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class EventFinder extends Finder
{
    const INFO  = 'info';
    const ERROR = 'error';

    /**
     * Create an event for a deployment
     *
     * @param string $type
     * @param \App\Model\Deployment $deployment
     * @param string $header
     * @param string $detail
     * @return \App\Model\Event
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function event(string $type, Deployment $deployment, string $header, string $detail = '')
    {
        $event = new Event;
        $event->deployment = $deployment->id;
        $event->type = $type;
        $event->header = $header;
        $event->detail = $detail;

        if (!$event->save()) {
            return false;
        }

        return $event;
    }
}
