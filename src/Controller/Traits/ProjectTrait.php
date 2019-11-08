<?php

namespace App\Controller\Traits;

use App\Model\Project;
use Ronanchilvers\Orm\Orm;

/**
 * Controller trait for project controllers
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
trait ProjectTrait
{
    /**
     * Get a project from an args array
     *
     * @param array $args
     * @return \App\Model\Project|null
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function projectFromArgs($args)
    {
        $project = Orm::finder(Project::class)->forKey($args['key']);
        if ($project instanceof Project) {
            return $project;
        }

        return null;
    }
}
