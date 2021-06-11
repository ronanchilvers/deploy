<?php

namespace App\Controller;

use App\Controller\Traits\ProjectTrait;
use App\Facades\Log;
use App\Facades\Provider;
use App\Facades\Router;
use App\Facades\Security;
use App\Facades\Session;
use App\Facades\View;
use App\Model\Deployment;
use App\Model\Event;
use App\Model\Project;
use App\Queue\DeployJob;
use App\Queue\ReactivateJob;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ronanchilvers\Foundation\Facade\Queue;
use Ronanchilvers\Foundation\Queue\Exception\FailedDispatchException;
use Ronanchilvers\Orm\Orm;
use Ronanchilvers\Utility\Str;
use RuntimeException;

/**
 * Controller for the index
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ProjectController
{
    use ProjectTrait;

    /**
     * Index action
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function index(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {
        $user = Security::user();
        $all = Orm::finder(Project::class)->all();

        $userFavourites = $user->preference('favourites', []);
        $favourites = $projects = [];
        foreach ($all as $project) {
            if (in_array($project->id, $userFavourites)) {
                $favourites[] = $project;
                continue;
            }
            $projects[] = $project;
        }

        return View::render(
            $response,
            'project/index.html.twig',
            [
                'favourites' => $favourites,
                'projects'   => $projects
            ]
        );
    }

    /**
     * View a project dashboard
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function view(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $args
    ) {
        if (!$project = $this->projectFromArgs($args)) {
            return $response->withRedirect(
                Router::pathFor('project.index')
            );
        }
        $finder = Orm::finder(Deployment::class);
        $deployments = $finder->forProject($project);

        $selected_number = $request->getQueryParam(
            'deployment',
            (0 < count($deployments)) ? $deployments[0]->number : false
        );
        $selectedDeployment = (0 < count($deployments)) ? $deployments[0] : false;
        foreach ($deployments as $deployment) {
            if ($deployment->number == $selected_number) {
                $selectedDeployment = $deployment;
                break;
            }
        }
        $events = [];
        if ($selectedDeployment) {
            $events = Orm::finder(Event::class)->arrayForDeploymentId(
                $selectedDeployment->id
            );
            // $events = $selectedDeployment->events;
        }

        return View::render(
            $response,
            'project/view.html.twig',
            [
                'project'             => $project,
                'deployments'         => $deployments,
                'selected_deployment' => $selectedDeployment,
                'events'              => $events,
            ]
        );
    }

    /**
     * Add a project
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function add(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {
        $project = new Project;
        if ('POST' == $request->getMethod()) {
            $data = $request->getParsedBody()['project'];
            $project->fromArray($data);
            if ($project->saveWithValidation()) {
                Session::flash([
                    'heading' => 'Project added'
                ], 'success');
                return $response->withRedirect(
                    Router::pathFor('project.view', ['key' => $project->key])
                );
            }
            Log::debug('Project add failed', [
                'errors' => $project->getErrors()
            ]);
        }

        return View::render(
            $response,
            'project/add.html.twig',
            [
                'project' => $project,
                'providers' => Provider::getOptions(),
            ]
        );
    }

    /**
     * Edit action
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function edit(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $args
    ) {
        if (!$project = $this->projectFromArgs($args)) {
            return $response->withRedirect(
                Router::pathFor('project.index')
            );
        }
        if ('POST' == $request->getMethod()) {
            $data = $request->getParsedBody()['project'];
            $project->fromArray($data);
            if ($project->saveWithValidation()) {
                Session::flash([
                    'heading' => 'Project saved'
                ]);
                return $response->withRedirect(
                    Router::pathFor('project.edit', [
                        'key' => $project->key
                    ])
                );
            }
        }

        return View::render(
            $response,
            'project/edit.html.twig',
            [
                'project' => $project,
                'providers' => Provider::getOptions(),
            ]
        );
    }

    /**
     * Prepare a deployment
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function prepareDeploy(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $args
    ) {
        if (!$project = $this->projectFromArgs($args)) {
            return $response->withRedirect(
                Router::pathFor('project.index')
            );
        }
        $provider = Provider::forProject($project);
        $tagsBranches = $provider->getTagsAndBranches($project->repository);

        return View::render(
            $response,
            'project/prepare-deploy.html.twig',
            [
                'project'       => $project,
                'tags_branches' => $tagsBranches,
            ]
        );
    }

    /**
     * Trigger a deploy for a project
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function deploy(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $args
    ) {
        try {
            if (!$project = $this->projectFromArgs($args)) {
                return $response->withRedirect(
                    Router::pathFor('project.index')
                );
            }
            if (!$project->isDeployable()) {
                throw new RuntimeException('Project is not deployable at the moment');
            }
            $input  = $request->getParsedBodyParam('project', []);
            $branch = (!isset($input['branch']) || empty($input['branch'])) ? $project->branch : $input['branch'];
            $provider = Provider::forProject(
                $project
            );
            $finder = Orm::finder(Event::class);
            Orm::transaction(function() use ($project, $provider, $branch, $response, $finder) {
                try {
                    $deployment = Orm::finder(Deployment::class)->nextForProject(
                        $project
                    );
                    $deployment->source = Security::email();
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
                        return $response->withRedirect(
                            Router::pathFor('project.view', [
                                'key' => $project->key
                            ])
                        );
                    }
                    if (!$project->markDeploying()) {
                        throw new RuntimeException('Unable to mark project as deploying');
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

            Session::flash([
                'heading' => 'Deploy queued successfully'
            ]);
        } catch (Exception $ex) {
            $message = [$ex->getMessage()];
            if ($previous = $ex->getPrevious()) {
                $message[] = $previous->getMessage();
            }
            $message = implode(' - ', $message);
            Session::flash(
                [
                    'heading' => 'Failed to initialise new deployment',
                    'content' => get_class($ex) . ' : ' . $message,
                ],
                'error'
            );
            Log::error('Failed to initialise new deployment', [
                'exception' => $ex,
            ]);
        }

        return $response->withRedirect(
            Router::pathFor('project.view', [
                'key' => $project->key
            ])
        );
    }

    /**
     * Trigger a deploy for a project
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function redeploy(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $args
    ) {
        try {
            if (!$project = $this->projectFromArgs($args)) {
                return $response->withRedirect(
                    Router::pathFor('project.index')
                );
            }
            Orm::transaction(function() use ($project, $args) {
                $dummy = Orm::finder(Deployment::class)->nextForProject(
                    $project
                );
                $original = Orm::finder(Deployment::class)->one(
                    $args['deployment']
                );
                if (!$original instanceof Deployment) {
                    throw new RuntimeException('Invalid attempt to re-deploy non-existant deployment');
                }
                $deployment           = clone $original;
                $deployment->original = $original;
                $deployment->number   = $dummy->number;
                if (!$deployment->save()) {
                    Log::debug('Unable to create deployment object', [
                        'project' => $project->toArray(),
                    ]);
                    throw new RuntimeException('Unable to create new deployment');
                }
                if (!$project->markDeploying()) {
                    throw new RuntimeException('Unable to mark project as deploying');
                }
                Queue::dispatch(
                    new ReactivateJob($original, $deployment)
                );
            });
            Session::flash([
                'heading' => 'Re-deploy queued successfully'
            ]);
        } catch (Exception $ex) {
            Session::flash(
                [
                    'heading' => 'Failed to initialise re-deployment',
                    'content' => $ex->getMessage(),
                ],
                'error'
            );
            Log::error('Failed to initialise re-deployment', [
                'exception' => $ex,
            ]);
        }

        return $response->withRedirect(
            Router::pathFor('project.view', [
                'key' => $project->key
            ])
        );
    }
}
