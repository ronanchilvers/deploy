<?php

namespace App\Action;

use App\Action\Context;
use App\Model\Deployment;
use App\Model\Finder\EventFinder;
use ReflectionClass;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Foundation\Traits\Optionable;
use Ronanchilvers\Utility\Str;

/**
 * Action to symlink the deployment in to the live location
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
abstract class AbstractAction
{
    use Optionable;

    /**
     * @var App\Model\Finder\EventFinder
     */
    protected $eventFinder = null;

    /**
     * @see App\Action\ActionInterface::getKey()
     */
    public function getKey()
    {
        $reflection = new ReflectionClass($this);
        $name       = Str::snake(
            str_replace(
                'Action',
                '',
                $reflection->getShortName()
            )
        );

        return str_replace('_', ' ', $name);
    }

    /**
     * Set the event finder for this action
     *
     * @param App\Model\Finder\EventFinder $eventFinder
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function setEventFinder(EventFinder $eventFinder)
    {
        $this->eventFinder = $eventFinder;
    }

    /**
     * Log an info event
     *
     * @param App\Model\Deployment $deployment
     * @param string $header
     * @param mixed $detail
     * @return App\Model\Event
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function info(Deployment $deployment, string $header, $detail = '')
    {
        if (is_array($detail)) {
            $detail = implode("\n", $detail);
        }
        return $this->eventFinder->event(
            EventFinder::INFO,
            $deployment,
            $header,
            $detail
        );
    }

    /**
     * Log an error event
     *
     * @param App\Model\Deployment $deployment
     * @param string $header
     * @param mixed $detail
     * @return App\Model\Event
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function error(Deployment $deployment, string $header, $detail = '')
    {
        if (is_array($detail)) {
            $detail = implode("\n", $detail);
        }
        return $this->eventFinder->event(
            EventFinder::ERROR,
            $deployment,
            $header,
            $detail
        );
    }

    /**
     * @see App\Action\ActionInterface::run()
     */
    abstract public function run(Config $configuration, Context $context);
}
