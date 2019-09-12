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
 * Action to symlink the release in to the live location
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
        $releaseDir = $context->getOrThrow('release_dir', 'Missing or invalid release directory');
        $linkFilename = File::join($projectDir, 'current');
        Log::debug('Preparing to symlink new release', [
            'release_dir' => $releaseDir,
            'link_name' => $linkFilename,
        ]);
        if (file_exists($linkFilename)) {
            Log::debug('Removing existing symlink', [
                'release_dir' => $releaseDir,
                'link_name' => $linkFilename,
            ]);
            if (!unlink($linkFilename)) {
                Log::error('Unable to remove existing symlink', [
                    'release_dir' => $releaseDir,
                    'link_name' => $linkFilename,
                ]);
                throw new RuntimeException('Unable to remove symlink prior to linking new release');
            }
        }
        Log::debug('Creating release symlink', [
            'release_dir' => $releaseDir,
            'link_name' => $linkFilename,
        ]);
        if (!symlink($releaseDir, $linkFilename)) {
            Log::debug('Unable to create symlink', [
                'release_dir' => $releaseDir,
                'link_name' => $linkFilename,
            ]);
            throw new RuntimeException('Unable to activate release symlink');
        }
    }
}
