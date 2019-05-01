<?php

namespace App\Provider;

use App\Provider\StrategyInterface;

/**
 * Strategy for projects stored in github
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class GithubStrategy extends AbstractStrategy implements StrategyInterface
{
    /**
     * Get the deployment config from the repository
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getDeployConfig(): ?string
    {
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

        return sprintf(
            "https://github.com/%s.git",
            $repository
        );
    }
}
