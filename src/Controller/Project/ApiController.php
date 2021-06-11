<?php

namespace App\Controller\Project;

use App\Controller\Traits\ApiTrait;
use App\Controller\Traits\ProjectTrait;
use App\Facades\Log;
use App\Facades\Provider;
use App\Facades\Security;
use App\Model\Deployment;
use App\Model\Event;
use App\Model\Project;
use App\Queue\DeployJob;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ronanchilvers\Foundation\Facade\Queue;
use Ronanchilvers\Orm\Orm;
use RuntimeException;

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
     * Trigger a build of a project specified by the project token
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function webhookDeploy(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $args
    ) {
        try {
            $project = Orm::finder(Project::class)->forToken($args['token']);
            if (!$project instanceof Project) {
                throw new RuntimeException(
                    'Invalid or unknown project token',
                    400
                );
            }
            if (!$project->isDeployable()) {
                throw new RuntimeException(
                    'Project is not deployable at the moment',
                    400
                );
            }
            $branch = $request->getQueryParam('branch', null);
            if (!is_null($branch)) {
                $branch = filter_var($branch, FILTER_SANITIZE_STRING);
            } else {
                $branch = $project->branch;
            }
            Log::debug("Queueing project from webhook for branch {$branch}", [
                'project' => $project->toArray(),
            ]);
            $provider = Provider::forProject(
                $project
            );
            $finder = Orm::finder(Event::class);
            Orm::transaction(function() use ($project, $provider, $finder, $branch) {
                try {
                    $deployment = Orm::finder(Deployment::class)->nextForProject(
                        $project
                    );
                    $deployment->source = 'webhook'; //Security::email();
                    if (!$deployment->save()) {
                        Log::debug('Unable to create new deployment object', [
                            'project' => $project->toArray(),
                        ]);
                        throw new RuntimeException('Unable to create new deployment');
                    }
                    $finder->event(
                        'info',
                        $deployment,
                        'Initialise',
                        sprintf("Querying %s for head commit data", $provider->getLabel())
                    );
                    $head = $provider->getHeadInfo($project->repository, $branch);
                    $finder->event(
                        'info',
                        $deployment,
                        'Initialise',
                        "Commit data : " . json_encode($head, JSON_PRETTY_PRINT)
                    );
                    Log::debug('Updating deployment commit information', $head);
                    $deployment->branch    = $branch;
                    $deployment->sha       = $head['sha'];
                    $deployment->author    = $head['author'];
                    $deployment->committer = $head['committer'];
                    $deployment->message   = $head['message'];
                    if (!$deployment->save()) {
                        throw new RuntimeException(
                            'Unable to create new deployment',
                            500
                        );
                    }
                    if (!$project->markDeploying()) {
                        throw new RuntimeException(
                            'Unable to mark project as deploying',
                            500
                        );
                    }
                    Queue::dispatch(
                        new DeployJob($deployment)
                    );
                } catch (Exception $ex) {
                    if (isset($deployment) && $deployment instanceof Deployment) {
                        $finder->event(
                            'error',
                            $deployment,
                            'Initialise',
                            $ex->getMessage()
                        );
                    }
                    throw $ex;
                }
            });

            Log::error('Queued deployment from webhook', [
                'project' => $project->toArray(),
            ]);
            return $this->apiResponse(
                $response,
                []
            );
        } catch (Exception $ex) {
            Log::error('Failed to initialise new deployment', [
                'exception' => $ex,
            ]);
            return $this->apiError(
                $response,
                $ex->getMessage(),
                $ex->getCode()
            );
        }
    }
}
