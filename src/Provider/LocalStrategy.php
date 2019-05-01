<?php

namespace App\Provider;

use App\Model\Project;
use App\Provider\StrategyInterface;

/**
 * Strategy for projects stored locally on disk
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class LocalStrategy implements StrategyInterface
{
    /**
     * {@inheritdoc}
     *
     * For local strategies the project repository is the absolute disk path.
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getDeployConfig(Project $project): ?string
    {
        $configPath = sprintf(
            '%s/%s',
            $project->repository,
            'deploy.yaml'
        );
        if (file_exists($configPath)) {
            $yaml = file_get_contents($configPath);

            return $yaml;
        }
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

        return $repository;
    }
}
