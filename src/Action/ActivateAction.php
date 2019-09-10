<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use App\Builder;
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
        $projectDir = $context->get('project_base_dir');
        $releaseDir = $context->get('release_dir');
        if (is_null($projectDir) || is_null($releaseDir)) {
            throw new RuntimeException('Missing or invalid project or release directory');
        }
        $linkFilename = File::join($projectDir, 'current');
        if (file_exists($linkFilename)) {
            if (!unlink($linkFilename)) {
                throw new RuntimeException('Unable to remove symlink prior to linking new release');
            }
        }
        if (!symlink($releaseDir, $linkFilename)) {
            throw new RuntimeException('Unable to activate release symlink');
        }
    }
}
