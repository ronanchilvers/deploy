<?php

namespace App\Provider;

use App\Model\Project;
use App\Provider\StrategyInterface;

/**
 * Strategy for projects stored in github
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class GithubStrategy implements StrategyInterface
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
            "https://github.com/%s.git",
            $repository
        );
    }
}
