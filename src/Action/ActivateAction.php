<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use App\Builder;
use App\Facades\Log;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Utility\File;
use RuntimeException;

/**
 * Action to symlink the deployment in to the live location
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ActivateAction extends AbstractAction implements ActionInterface
{
    /**
     * @see App\Action\ActionInterface::run()
     */
    public function run(Config $configuration, Context $context)
    {
        $projectDir = $context->getOrThrow('project_base_dir', 'Missing or invalid project base directory');
        $deploymentDir = $context->getOrThrow('deployment_dir', 'Missing or invalid deployment directory');
        $linkFilename = File::join($projectDir, 'current');
        Log::debug('Preparing to symlink new deployment', [
            'deployment_dir' => $deploymentDir,
            'link_name' => $linkFilename,
        ]);
        if (file_exists($linkFilename)) {
            Log::debug('Removing existing symlink', [
                'deployment_dir' => $deploymentDir,
                'link_name' => $linkFilename,
            ]);
            if (!unlink($linkFilename)) {
                Log::error('Unable to remove existing symlink', [
                    'deployment_dir' => $deploymentDir,
                    'link_name' => $linkFilename,
                ]);
                throw new RuntimeException('Unable to remove symlink prior to linking new deployment');
            }
        }
        Log::debug('Creating deployment symlink', [
            'deployment_dir' => $deploymentDir,
            'link_name' => $linkFilename,
        ]);
        if (!symlink($deploymentDir, $linkFilename)) {
            Log::debug('Unable to create symlink', [
                'deployment_dir' => $deploymentDir,
                'link_name' => $linkFilename,
            ]);
            throw new RuntimeException('Unable to activate deployment symlink');
        }
    }
}
