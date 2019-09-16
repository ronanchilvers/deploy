<?php

namespace App\Controller;

use App\Facades\Router;
use App\Facades\View;
use App\Model\Project;
use App\Model\Release;
use App\Queue\DeployJob;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ronanchilvers\Foundation\Facade\Queue;
use Ronanchilvers\Orm\Orm;

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
        $projects = Orm::finder(Project::class)->all();

        return View::render(
            $response,
            'project/index.html.twig',
            [
                'projects' => $projects
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

        $finder = Orm::finder(Release::class);
        $releases = $finder->forProject($project);
        $lastRelease = false;
        if (0 < count($releases)) {
            $lastRelease = $releases[0];
        }

        return View::render(
            $response,
            'project/view.html.twig',
            [
                'project'  => $project,
                'releases' => $releases,
                'last_release' => $lastRelease,
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
        $project = new Project();
        if ($request->isMethod('POST')) {
            $data = $request->getParsedBody()['project'];
            $project->fromArray($data);
            if ($project->saveWithValidation()) {
                return $response->withRedirect(
                    Router::pathFor('project.index')
                );
            }
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
                return $response->withRedirect(
                    Router::pathFor('project.edit', [
                        'id' => $project->id
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
     * Trigger a deploy for a project
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function deploy(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $args
    ) {
        if (!$project = $this->projectFromArgs($args)) {
            return $response->withRedirect(
                Router::pathFor('project.index')
            );
        }
        $release = Orm::finder(Release::class)->nextForProject(
            $project
        );
        $release->save();
        Queue::dispatch(
            new DeployJob($release)
        );

        return $response->withRedirect(
            Router::pathFor('project.view', [
                'id' => $project->id
            ])
        );
    }

    /**
     * Get a project from an args array
     *
     * @param array $args
     * @return App\Model\Project|null
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function projectFromArgs($args)
    {
        $project = Orm::finder(Project::class)->one($args['id']);
        if ($project instanceof Project) {
            return $project;
        }

        return null;
    }
}
