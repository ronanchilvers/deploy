<?php

namespace App\Model\Finder;

use App\Model\Project;
use App\Model\Deployment;
use Ronanchilvers\Orm\Finder;
use ClanCats\Hydrahon\Query\Expression;

/**
 * Finder for deployment models
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class DeploymentFinder extends Finder
{
    /**
     * Get the deployments for a project
     *
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function forProject(Project $project)
    {
        return $this->select()
            ->where(Deployment::prefix('project'), $project->id)
            ->orderBy(Deployment::prefix('number'), 'desc')
            ->execute();
    }

    /**
     * Get the latest deployment
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function lastForProject()
    {
        return $this->select()
            ->where(Deployment::prefix('project'), $project->id)
            ->orderBy(Deployment::prefix('number'), 'desc')
            ->one();
    }

    /**
     * Get the next deployment for a project
     *
     * This method returns a new unsaved deployment with the correct deployment number.
     *
     * @param \App\Model\Project $project
     * @return \App\Model\deployment
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function nextForProject(Project $project)
    {
        $existing = $this->select()
            ->where(Deployment::prefix('project'), $project->id)
            ->orderBy(Deployment::prefix('number'), 'desc')
            ->one();
        if ($existing instanceof Deployment) {
            $number = $existing->number;
        } else {
            $number = 0;
        }
        $deployment          = new Deployment;
        $deployment->project = $project->id;
        $deployment->number  = ++$number;

        return $deployment;
    }

    /**
     * Get an array of deployments with a number lower than a specified one
     *
     * @param \App\Model\Project $project
     * @param int $number
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function earlierThan(Project $project, $number)
    {
        $sql = "SELECT *
                FROM deployments
                WHERE deployment_project = :project AND
                      deployment_number <= (
                          SELECT MAX(deployment_number)
                          FROM deployments
                          WHERE deployment_project = :project
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
