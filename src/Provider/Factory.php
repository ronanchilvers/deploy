<?php

namespace App\Provider;

use App\Model\Project;
use App\Provider\ProviderInterface;
use RuntimeException;

/**
 * Provider factory responsible for managing provider instances
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Factory
{
    /**
     * @var array
     */
    protected $instances = [];

    /**
     * Add a provider instance
     *
     * @param \App\Provider\ProviderInterface
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function addProvider(ProviderInterface $instance)
    {
        $this->instances[] = $instance;
    }

    /**
     * Get a suitable instance for a given project
     *
     * @param \App\Model\Project $project
     * @return \App\Provider\ProviderInterface
     * @throws RuntimeException If a suitable provider is not found
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function forProject(Project $project)
    {
        foreach ($this->instances as $instance) {
            if ($instance->handles($project)) {
                return $instance;
            }
        }

        throw new RuntimeException('No suitable instance found for project provider ' . $project->provider);
    }
}
