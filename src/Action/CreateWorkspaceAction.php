<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use App\Builder;
use App\Facades\Log;
use App\Facades\Settings;
use Ronanchilvers\Foundation\Config;
use RuntimeException;

/**
 * Action to create the workspace for a given project
 *
 * - calculates the base paths for the project workspace
 * - ensures the base paths all exist
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class CreateWorkspaceAction extends AbstractAction
{
    /**
     * @see \App\Action\ActionInterface::run()
     */
    public function run(Config $configuration, Context $context)
    {
        $projectDir    = $context->getOrThrow('project_base_dir', 'Invalid or missing project base directory');
        $deploymentDir = $context->getOrThrow('deployment_base_dir', 'Invalid or missing deployment base directory');
        $deployment    = $context->getOrThrow('deployment', 'Invalid or missing deployment');
        $locations     = [$projectDir, $deploymentDir];
        // $this->info(
        //     $deployment,
        //     [
        //         'Checking workspace exists for project',
        //         'Locations - ' . implode(', ', $locations),
        //     ]
        // );
        $mode = Settings::get('build.chmod.default_folder', Builder::MODE_DEFAULT);
        foreach ($locations as $location) {
            if (is_dir($location)) {
                $this->info(
                    $deployment,
                    [
                        'Location exists: ' . $location,
                    ]
                );
                Log::debug('Build directory exists', [
                    'location' => $location,
                ]);
                continue;
            }
            Log::debug('Creating build directory', [
                'location' => $location,
                'mode' => $mode,
            ]);
            if (!mkdir($location, $mode, true)) {
                $this->error(
                    $deployment,
                    [
                        "Failed to create location: {$location} (mode {$mode})",
                    ]
                );
                Log::error('Unable to create build directory', [
                    'location' => $location,
                    'mode' => $mode,
                ]);
                throw new RuntimeException(
                    'Unable to create build directory at ' . $location
                );
            }
            $this->info(
                $deployment,
                [
                    "Created location: {$location} (mode {$mode})",
                ]
            );
        }
    }
}
