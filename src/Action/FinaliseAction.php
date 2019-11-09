<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use Carbon\Carbon;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Utility\File;
use RuntimeException;

/**
 * Action to finalise the deployment
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class FinaliseAction extends AbstractAction
{
    /**
     * @see \App\Action\ActionInterface::run()
     */
    public function run(Config $configuration, Context $context)
    {
        $deployment    = $context->getOrThrow('deployment', 'Invalid or missing project');
        $deploymentDir = $context->get('deployment_dir', null);
        if (is_null($deploymentDir)) {
            return;
        }
        $filename = File::join($deploymentDir, '.deploy_info');
        if (file_exists($filename)) {
            return;
        }
        $info = [
            'sha'       => $deployment->sha,
            'deployed'  => $deployment->started->format('Y-m-d H:i:s'),
            'author'    => $deployment->author,
            'committer' => $deployment->committer,
        ];
        file_put_contents(
            $filename,
            json_encode($info, JSON_PRETTY_PRINT) . "\n"
        );
    }
}
