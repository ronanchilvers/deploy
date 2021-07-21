<?php

namespace App\Controller\Project;

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
class SettingsController
{
    use ProjectTrait;

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
            '@web/project/edit.html.twig',
            [
                'project' => $project,
                'providers' => Provider::getOptions(),
            ]
        );
    }

    /**
     * Webhook settings
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function webhooks(
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
            '@web/project/webhooks.html.twig',
            [
                'project' => $project,
                'providers' => Provider::getOptions(),
            ]
        );
    }
}
