<?php

namespace App;

use App\Action\ActionInterface;
use App\Action\Context;
use App\Facades\Log;
use App\Model\Project;
use App\Model\Release;
use Closure;
use Ronanchilvers\Foundation\Config;
use SplQueue;

/**
 * The builder is responsible for building a new release from a given repository
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Builder
{
    const STAGE_INITIALISE   = 'initialise';
    const STAGE_PREPARE      = 'prepare';
    const STAGE_FINALISE     = 'finalise';

    const MODE_DEFAULT       = 0770;
    const MODE_WRITABLE_FILE = 0660;
    const MODE_WRITABLE_DIR  = 0770;

    /**
     * @var App\Model\Project
     */
    protected $project;

    /**
     * @var App\Model\Release
     */
    protected $release;

    /**
     * @var array<Action>
     */
    protected $actions = null;

    /**
     * Class constructor
     *
     * @param App\Model\Project $project
     * @param App\Model\Release $release
     * @param Ronanchilvers\Foundation\Config $configuration
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(
        Project $project,
        Release $release,
        Config $configuration
    ) {
        $this->project       = $project;
        $this->release       = $release;
        $this->actions       = new SplQueue;
        $this->actions->setIteratorMode(SplQueue::IT_MODE_DELETE);
    }

    /**
     * Add an action to the builder
     *
     * @param App\Action\ActionInterface
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
    public function run(Config $configuration, Closure $closure = null)
    {
        if (is_null($closure)) {
            $closure = function ($string) {
                Log::debug($string);
            };
        }

        $context = new Context();
        $context->set('project', $this->project);
        $context->set('release', $this->release);

        foreach ($this->actions as $action) {
            $closure('Running action: ' . $action->getName());
            $action->run(
                $configuration,
                $context
            );
        }
    }
}
