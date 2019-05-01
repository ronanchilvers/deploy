<?php

namespace App\Provider;

use App\Model\Project;
use App\Provider\StrategyInterface;

/**
 * Strategy for projects stored in gitlab
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class GitlabStrategy implements StrategyInterface
{
    /**
     * Get the deployment config from the repository
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getDeployConfig(Project $project): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getCloneUrl(Project $project): string
    {
        $repository = $project->repository;

        return sprintf(
            "https://gitlab.com/%s.git",
            $repository
        );
    }
}
