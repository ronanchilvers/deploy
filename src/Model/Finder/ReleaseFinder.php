<?php

namespace App\Model\Finder;

use App\Model\Project;
use App\Model\Release;
use Ronanchilvers\Orm\Finder;

/**
 * Finder for release models
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ReleaseFinder extends Finder
{
    /**
     * Get the next release for a project
     *
     * This method returns a new unsaved release with the correct release number.
     *
     * @param App\Model\Project $project
     * @return App\Model\Release
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function nextForProject(Project $project)
    {
        $existing = $this->select()
            ->where(Project::prefix('project'), $project->id)
            ->orderBy(Project::prefix('number'), 'desc')
            ->one();
        if ($existing instanceof Release) {
            $number = $existing->number;
        } else {
            $number = 0;
        }
        $release          = new Release;
        $release->project = $project->id;
        $release->status  = 'new';
        $release->number  = ++$number;

        return $release;
    }
}
