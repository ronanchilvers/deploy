<?php

namespace App\Controller;

use App\Facades\Router;
use App\Facades\View;
use App\MessageCollection;
use App\Model\Project;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
            if ($project->validate() && $project->save()) {
                return $response->withRedirect(
                    Router::pathFor('project.index')
                );
            }
            $errors = $project->getErrors();
        }

        return View::render(
            $response,
            'project/add.html.twig',
            [
                'errors'  => $errors,
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
        $project = Orm::finder(Project::class)->one($args['id']);
        if (!$project instanceof Project) {
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
            $errors = $project->getErrors();
        }

        return View::render(
            $response,
            'project/edit.html.twig',
            [
                'errors'  => $errors,
                'project' => $project
            ]
        );
    }
}
