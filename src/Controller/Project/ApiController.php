<?php

namespace App\Controller\Project;

use App\Controller\Traits\ApiTrait;
use App\Controller\Traits\ProjectTrait;
use App\Model\Deployment;
use App\Model\Event;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ronanchilvers\Orm\Orm;

/**
 * API Controller supporting the project UI
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ApiController
{
    use ProjectTrait;
    use ApiTrait;

    /**
     * Get the event data for a project
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function events(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        if (!$project = $this->projectFromArgs($args)) {
            return $this->apiError(
                $response,
                'Invalid project'
            );
        }
        if (!isset($args['number']) || 0 == (int) $args['number']) {
            return $this->apiError(
                $response,
                'Invalid deployment number'
            );
        }
        $number = (int) $args['number'];
        $deployment = Orm::finder(Deployment::class)->forProjectIdAndNumber(
            $project->id,
            $number
        );
        if (!$deployment instanceof Deployment) {
            return $this->apiError(
                $response,
                'Deployment not found for project'
            );
        }
        $data = [
            // 'project' => $project->toArray(),
            'deployment' => $deployment->toArray(),
        ];
        $events = Orm::finder(Event::class)->arrayForDeploymentId(
            $deployment->id
        );
        $data['events'] = $events;

        return $this->apiResponse(
            $response,
            $data
        );
    }

    /**
     *
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function branchesAndTags(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $args
    ) {
        if (!$project = $this->projectFromArgs($args)) {
            return $this->apiError(
                $response,
                'Invalid project'
            );
        }
        try {
            $provider = \App\Facades\Provider::forProject($project);
            $data = $provider->getTagsAndBranches($project->repository);

            return $this->apiResponse(
                $response,
                $data
            );
        } catch (Exception $ex) {
            return $this->apiError(
                $response,
                'Unable to query for branch / tag information',
                500
            );
        }
    }
}
