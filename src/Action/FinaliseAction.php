<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use App\Action\HookableInterface;
use App\Action\Traits\Hookable;
use Carbon\Carbon;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Utility\File;
use RuntimeException;

/**
 * Action to finalise the deployment
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class FinaliseAction extends AbstractAction implements
    ActionInterface,
    HookableInterface
{
    use Hookable;

    /**
     * @see App\Action\ActionInterface::run()
     */
    public function run(Config $configuration, Context $context)
    {
        $project = $context->getOrThrow('project', 'Invalid or missing project');
        $deployment = $context->getOrThrow('deployment', 'Invalid or missing project');
        $deployment->status = 'deployed';
        if (!$deployment->save()) {
            throw new RuntimeException('Unable to update the deployment status');
        }
        $project->last_number     = $deployment->number;
        $project->last_deployment = Carbon::now();
        $project->last_sha        = $deployment->sha;
        $project->last_author     = $deployment->author;
        $project->last_status     = $deployment->status;
        if (!$project->save()) {
            throw new RuntimeException('Unable to update last deployment date for project');
        }
        $deploymentDir = $context->get('deployment_dir', null);
        if (is_null($deploymentDir)) {
            return;
        }
        $info = [
            'SHA : ' . $deployment->sha,
            'Deployed : ' . $deployment->started->format('Y-m-d H:i:s'),
            'Author : ' . $deployment->author,
            'Committer : ' . $deployment->committer,
        ];
        file_put_contents(
            File::join($deploymentDir, '.deploy_info'),
            implode("\n", $info) . "\n"
        );
    }
}
