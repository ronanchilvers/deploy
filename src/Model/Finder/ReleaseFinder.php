<?php

namespace App\Model\Finder;

use App\Model\Project;
use App\Model\Release;
use Ronanchilvers\Orm\Finder;
use ClanCats\Hydrahon\Query\Expression;

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
            ->where(Release::prefix('project'), $project->id)
            ->orderBy(Release::prefix('number'), 'desc')
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

    /**
     * Get an array of releases with a number lower than a specified one
     *
     * @param App\Model\Project $project
     * @param int $number
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function earlierThan(Project $project, $number)
    {
        $sql = "SELECT *
                FROM releases
                WHERE release_project = 1 AND
                      release_number <= (
                          SELECT MAX(release_number)
                          FROM releases
                          WHERE release_project = :project
                      ) - :number";

        return $this->query(
            $sql,
            [
                'project' => $project->id,
                'number'  => $number
            ]
        );
    }
}
