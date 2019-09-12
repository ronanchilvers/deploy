<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use App\Action\Context;
use App\Builder;
use App\Facades\Log;
use App\Model\Release;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Orm\Orm;
use Ronanchilvers\Utility\File;

/**
 * Action to clean up old releases after deployment
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class CleanupAction extends AbstractAction implements ActionInterface
{
    /**
     * @see App\Action\ActionInterface::run()
     */
    public function run(Config $configuration, Context $context)
    {
        $releaseBaseDir = $context->getOrThrow(
            'release_base_dir',
            'Invalid or missing release dir'
        );
        $project  = $context->getOrThrow('project', 'Invalid or missing project');
        $number   = $configuration->get('cleanup.keep_releases', 5);
        $releases = Orm::finder(Release::class)->earlierThan(
            $project,
            $number
        );
        Log::debug(sprintf("Found %d releases to clean", count($releases)));
        if (0 == count($releases)) {
            return;
        }
        foreach ($releases as $release) {
            $releaseDir = File::join($releaseBaseDir, $release->id);
            Log::error('Cleaning old release', [
                'release_dir' => $releaseDir,
            ]);
            if (!File::rm($releaseDir)) {
                Log::error('Unable to remove old release directory', [
                    'release_dir' => $releaseDir,
                ]);
            }
            if (!$release->delete()) {
                Log::error('Unable to remove old release', [
                    'release' => $release->toArray(),
                ]);
            }
        }
    }
}
