<?php

namespace App\Provider;

use App\Provider\StrategyInterface;

/**
 * Strategy for projects stored locally on disk
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class LocalStrategy extends AbstractStrategy implements StrategyInterface
{
    /**
     * {@inheritdoc}
     *
     * For local strategies the project repository is the absolute disk path.
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getDeployConfig(): ?string
    {
        $configPath = sprintf(
            '%s/%s',
            $this->project()->repository,
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
    public function getCloneUrl(): string
    {
        $repository = $this->project()->repository;

        return $repository;
    }
}
