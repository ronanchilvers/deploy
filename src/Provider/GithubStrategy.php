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
     * {@inheritdoc}
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getCloneUrl()
    {
        $repository = $this->getProject()->repository;

        return sprintf(
            "https://github.com/%s.git",
            $repository
        );
    }
}
