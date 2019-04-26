<?php

namespace App\Model\Observer;

use App\Model\Project;
use Ronanchilvers\Utility\Str;

/**
 * Observer for project models
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ProjectObserver
{
    /**
     * Handle creation tasks
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function creating(Project $project)
    {
        if (empty($project->token)) {
            $project->token = Str::token(64);
        }
    }
}
