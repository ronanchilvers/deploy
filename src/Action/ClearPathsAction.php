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
 * Action to remove specific paths from the deployment prior to symlinking
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ClearPathsAction extends AbstractAction
{
    /**
     * @see \App\Action\ActionInterface::run()
     */
    public function run(Config $configuration, Context $context)
    {
        $deployment    = $context->getOrThrow('deployment', 'Invalid or missing deployment');
        $deploymentDir = $context->getOrThrow('deployment_dir', 'Missing or invalid deployment directory');
        Log::debug('Preparing to clear paths from deployment', [
            'deployment'     => $deployment->toArray(),
            'deployment_dir' => $deploymentDir,
        ]);
        $clearPaths = $configuration->get('clear_paths.paths', []);
        if (0 == count($clearPaths)) {
            $this->info(
                $deployment,
                'No paths configured for clearing'
            );
            return;
        }
        foreach ($clearPaths as $path) {
            $fullPath = File::join($deploymentDir, $path);
            // Is readable works for both files and directories
            if (!is_readable($fullPath)) {
                $this->info(
                    $deployment,
                    [
                        'Path to clear was not found when clearing - ' . $path,
                        'Full path - ' . $fullPath,
                    ]
                );
                continue;
            }
            if (!File::rm($fullPath)) {
                $this->error(
                    $deployment,
                    [
                        'Path to clear couldn\'t be removed',
                        'Full path - ' . $fullPath,
                    ]
                );
                throw new RuntimeException('Unable to remove clear path - ' . $path);
            }
            $this->info(
                $deployment,
                [
                    'Cleared path - ' . $path,
                    'Full path - ' . $fullPath,
                ]
            );
        }
    }
}
