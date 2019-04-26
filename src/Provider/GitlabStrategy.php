<?php

namespace App\Provider;

use App\Provider\StrategyInterface;

/**
 * Strategy for projects stored in gitlab
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class GitlabStrategy extends AbstractStrategy implements StrategyInterface
{
    /**
     * {@inheritdoc}
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getCloneUrl()
    {
        $repository = $this->getProject()->repository;

        return sprintf(
            "https://gitlab.com/%s.git",
            $repository
        );
    }
}
