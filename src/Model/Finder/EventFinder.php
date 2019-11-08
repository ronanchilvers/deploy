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
     * @return bool|\App\Model\Event
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function event(string $type, Deployment $deployment, string $header, string $detail = '')
    {
        $event = new Event;
        $event->deployment = $deployment;
        $event->type = $type;
        $event->header = $header;
        $event->detail = $detail;

        if (!$event->save()) {
            return false;
        }

        return $event;
    }

    /**
     * Get an event array for a deployment id
     *
     * @param int $deploymentId
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function arrayForDeploymentId($deploymentId)
    {
        $events = $this
            ->select()
            ->where('event_deployment', $deploymentId)
            ->orderby('event_created')
            ->execute();
        if (empty($events)) {
            return [];
        }

        $arr = [];
        $header = false;
        $lastEvent = false;
        foreach ($events as $event) {
            if ($header !== $event->header) {
                if (isset($arr[$header]['times'])) {
                    $arr[$header]['times']['end'] = $lastEvent->created;
                    $arr[$header]['times']['duration'] = $arr[$header]['times']['end']->diffInSeconds(
                        $arr[$header]['times']['start']
                    );
                }
                $header       = $event->header;
                $arr[$header]['times'] = [
                    'start' => $event->created,
                ];
            }
            if (!isset($arr[$header]['type']) || 'error' !== $arr[$header]['type']) {
                $arr[$header]['type'] = $event->type;
            }
            if (!isset($arr[$header]['events'])) {
                $arr[$header]['events'] = [];
            }
            $arr[$header]['events'][] = $event->detail . "\n";
            $lastEvent = $event;
        }
        $arr[$header]['times']['end'] = $lastEvent->created;
        $arr[$header]['times']['duration'] = $arr[$header]['times']['end']->diffInSeconds(
            $arr[$header]['times']['start']
        );
        // @TODO Remove var_dump
        // echo '<pre>' . print_r($arr, true); exit();

        return $arr;
    }
}
