<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use App\Model\Release;
use Carbon\Carbon;
use Ronanchilvers\Foundation\Config;
use RuntimeException;

/**
 * Action to finalise the release
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
        $release = $context->getOrThrow('release', 'Invalid or missing project');
        $release->status = 'deployed';
        if (!$release->save()) {
            throw new RuntimeException('Unable to update the release status');
        }
        $project->last_number  = $release->number;
        $project->last_release = Carbon::now();
        $project->last_sha     = $release->sha;
        $project->last_author  = $release->author;
        if (!$project->save()) {
            throw new RuntimeException('Unable to update last release date for project');
        }
    }
}
