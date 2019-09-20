<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use Carbon\Carbon;
use Ronanchilvers\Foundation\Config;
use RuntimeException;

/**
 * Action to finalise the deployment
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class FinaliseAction extends AbstractAction implements ActionInterface
{
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
    }
}
