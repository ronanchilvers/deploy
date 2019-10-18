<?php

namespace App;

use App\Action\ActionInterface;
use App\Action\Context;
use App\Facades\Log;
use App\Model\Deployment;
use App\Model\Event;
use App\Model\Finder\EventFinder;
use App\Model\Project;
use Closure;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Orm\Orm;
use SplQueue;

/**
 * The builder is responsible for building a new deployment from a given repository
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Builder
{
    const STAGE_INITIALISE   = 'initialise';
    const STAGE_PREPARE      = 'prepare';
    const STAGE_FINALISE     = 'finalise';

    const MODE_DEFAULT       = 0770;
    const MODE_DEFAULT_FILE  = 0640;
    const MODE_WRITABLE_DIR  = 0770;

    /**
     * @var \App\Model\Project
     */
    protected $project;

    /**
     * @var \App\Model\Deployment
     */
    protected $deployment;

    /**
     * @var \SplQueue
     */
    protected $actions = null;

    /**
     * Class constructor
     *
     * @param \App\Model\Project $project
     * @param \App\Model\Deployment $deployment
     * @param \Ronanchilvers\Foundation\Config $configuration
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(
        Project $project,
        Deployment $deployment
    ) {
        $this->project    = $project;
        $this->deployment = $deployment;
        $this->actions    = new SplQueue;
        $this->actions->setIteratorMode(SplQueue::IT_MODE_DELETE);
    }

    /**
     * Add an action to the builder
     *
     * @param \App\Action\ActionInterface
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function addAction(ActionInterface $action)
    {
        $this->actions->enqueue($action);
    }

    /**
     * Run the builder
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function run(Config $configuration, Context $context = null, Closure $closure = null)
    {
        if (is_null($closure)) {
            $closure = function($string) {
                Log::debug($string);
            };
        }

        $eventFinder = Orm::finder(Event::class);
        if (!$context instanceof Context) {
            $context = new Context();
        }
        $context->set('project', $this->project);
        $context->set('deployment', $this->deployment);

        foreach ($this->actions as $action) {
            $header = 'Running action: ' . $action->getKey();
            $closure($header);
            $action->setEventFinder($eventFinder);
            if ($action->isHookable()) {
                $action->runHooks(
                    'before',
                    $configuration,
                    $context
                );
            }
            $action->run(
                $configuration,
                $context
            );
            if ($action->isHookable()) {
                $action->runHooks(
                    'after',
                    $configuration,
                    $context
                );
            }
        }
    }
}
