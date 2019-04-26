<?php

namespace App\Provider;

use App\Model\Project;
use App\Provider\StrategyInterface;

/**
 * Strategy for projects stored in gitlab
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
abstract class AbstractStrategy
{
    /**
     * @var App\Model\Project
     */
    protected $project;

    /**
     * Class constructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Get the strategy project
     *
     * @return App\Model\Project
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getProject()
    {
        return $this->project;
    }
}
