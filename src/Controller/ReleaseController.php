<?php

namespace App\Controller;

use App\Facades\Router;
use App\Facades\View;
use App\Model\Project;
use App\Model\Release;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ronanchilvers\Foundation\Facade\Queue;
use Ronanchilvers\Orm\Orm;

/**
 * Controller for releases
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ReleaseController
{
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
        $release = Orm::finder(Release::class)
            ->select()
            ->where(Release::prefix('project'), $args['project'])
            ->where(Release::primaryKey(), $args['release'])
            ->one()
            ;
        if (!$release instanceof Release) {
            return $response->withRedirect(
                Router::pathFor('project.index')
            );
        }
        $project = $release->project;

        return View::render(
            $response,
            'release/view.html.twig',
            [
                'project'  => $project,
                'releases' => $releases,
                'last_release' => $lastRelease,
            ]
        );
    }
}
