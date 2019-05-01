<?php

namespace App\Provider;

use App\Model\Project;

/**
 * Interface for provider strategy objects
 *
 * Provider strategies translate project information for a given version control provider
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
interface StrategyInterface
{
    /**
     * Get the deployment config from the repository
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getDeployConfig(Project $project): ?string;

    /**
     * Get the clone URL for this strategy
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getCloneUrl(Project $project): string;
}
