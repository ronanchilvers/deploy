<?php

namespace App\Controller;

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

        $finder   = Orm::finder(Deployment::class);
        $deployments = $finder->forProject($project);

        $selected_number = $request->getQueryParam(
            'deployment',
            (0 < count($deployments)) ? $deployments[0]->number : false
        );
        $selectedDeployment = (0 < count($deployments)) ? $deployments[0] : false ;
        foreach ($deployments as $deployment) {
            if ($deployment->number == $selected_number) {
                $selectedDeployment = $deployment;
                break;
            }
        }
        $events = [];
        if ($selectedDeployment) {
            $events = $selectedDeployment->events;
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
        ResponseInterface $response,
        $args
    ) {
        $project = new Project;
        if ($request->isMethod('POST')) {
            $data = $request->getParsedBody()['project'];
            $project->fromArray($data);
            if ($project->saveWithValidation()) {
                Session::flash([
                    'heading' => 'Project added'
                ], 'success');
                return $response->withRedirect(
                    Router::pathFor('project.view', [ 'key' => $project->key ])
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
        if ($request->isMethod('POST')) {
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
                'project' => $project
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

        return View::render(
            $response,
            'project/prepare-deploy.html.twig',
            [
                'project' => $project,
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
            $input  = $request->getParsedBodyParam('project', []);
            $branch = (!isset($input['branch']) || empty($input['branch'])) ? 'master' : $input['branch'];
            $provider = Provider::forProject(
                $project
            );
            Orm::transaction(function () use ($project, $provider, $branch) {
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
                $finder = Orm::finder(Event::class);
                $head = $provider->getHeadInfo(
                    $project->repository,
                    $branch,
                    function ($type, $header, $detail = '') use ($finder, $deployment) {
                        $finder->event(
                            $type,
                            $deployment,
                            $header,
                            $detail
                        );
                    }
                );
                Log::debug('Updating deployment commit information', $head);
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
                    'content' => $message,
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
            Orm::transaction(function () use ($project, $args) {
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
                $deployment->original = $original->id;
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
