<?php

namespace App\Action;

use App\Action\ActionInterface;
use App\Action\Context;
use App\Facades\Log;
use App\Model\Deployment;
use App\Model\Finder\EventFinder;
use ReflectionClass;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Foundation\Traits\Optionable;
use Ronanchilvers\Utility\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Action to symlink the deployment in to the live location
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
abstract class AbstractAction implements ActionInterface
{
    use Optionable;

    /**
     * @var \App\Model\Finder\EventFinder
     */
    protected $eventFinder = null;

    /**
     * @var boolean
     */
    protected $hookable = true;

    /**
     * @see \App\Action\ActionInterface::getKey()
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

        return $name;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function setEventFinder(EventFinder $eventFinder)
    {
        $this->eventFinder = $eventFinder;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function isHookable()
    {
        return $this->hookable;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function runHooks($hook, Config $configuration, Context $context)
    {
        $deployment = $context->getOrThrow(
            'deployment',
            'Invalid or missing deployment in hook runner'
        );
        $deploymentDir = $context->getOrThrow(
            'deployment_dir',
            'Invalid or missing deployment directory'
        );
        $hook = strtolower($hook);
        if (!in_array($hook, ['before', 'after'])) {
            return;
        }
        $key   = $this->getKey() . '.' . $hook;
        $hooks = $configuration->get($key);
        if (!is_array($hooks) || empty($hooks)) {
            Log::debug('No hooks defined', [
                'key' => $key
            ]);
            return;
        }
        foreach ($hooks as $command) {
            $this->info(
                $deployment,
                sprintf('Hook: %s hook running - %s', $key, $command)
            );
            $command = preg_replace('#[\s]{2,}#',' ', $command);
            $command = explode(" ", $command, 2);
            // @TODO Remove var_dump
            // var_dump($command); exit();
            $process = new Process($command, $deploymentDir);
            $process->run();
            if (!$process->isSuccessful()) {
                $this->error(
                    $deployment,
                    [
                        sprintf('Hook: %s hook failed to run : %s', $key, implode(' ', $command)),
                        $process->getOutput(),
                        $process->getErrorOutput()
                    ]
                );
                throw new RuntimeException('Unable to run deployment hook');
            }
            $this->info(
                $deployment,
                [
                    sprintf('Hook: %s hook ran successfully', $key),
                    $process->getOutput(),
                ]
            );
        }
    }

    /**
     * @see \App\Action\ActionInterface::run()
     */
    abstract public function run(Config $configuration, Context $context);

    /**
     * Log an info event
     *
     * @param \App\Model\Deployment $deployment
     * @param string $header
     * @param mixed $detail
     * @return bool|\App\Model\Event
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function info(Deployment $deployment, $detail = '')
    {
        return $this->event(
            EventFinder::INFO,
            $deployment,
            $detail
        );
    }

    /**
     * Log an error event
     *
     * @param \App\Model\Deployment $deployment
     * @param string $header
     * @param mixed $detail
     * @return bool|\App\Model\Event
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function error(Deployment $deployment, $detail = '')
    {
        return $this->event(
            EventFinder::ERROR,
            $deployment,
            $detail
        );
    }

    /**
     * Log an event
     *
     * @param string $type
     * @param \App\Model\Deployment $deployment
     * @param string $header
     * @param mixed $detail
     * @return bool|\App\Model\Event
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function event(string $type, Deployment $deployment, $detail = '')
    {
        if (is_array($detail)) {
            $detail = implode("\n", $detail);
        }
        $header = ucwords(str_replace('_', ' ', $this->getKey()));
        return $this->eventFinder->event(
            $type,
            $deployment,
            $header,
            $detail
        );
    }
}
