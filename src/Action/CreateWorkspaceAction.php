<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use App\Builder;
use App\Facades\Log;
use App\Facades\Settings;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Utility\File;
use RuntimeException;

/**
 * Action to create the workspace for a given project
 *
 * - calculates the base paths for the project workspace
 * - ensures the base paths all exist
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class CreateWorkspaceAction extends AbstractAction implements ActionInterface
{
    /**
     * @see App\Action\ActionInterface::run()
     */
    public function run(Config $configuration, Context $context)
    {
        $baseDir    = Settings::get('build.base_dir');
        $project    = $context->getOrThrow('project', 'Invalid or missing project');
        $deployment = $context->getOrThrow('deployment', 'Invalid or missing deployment');
        $key        = $project->key;
        $projectDir = File::join(
            $baseDir,
            $key
        );
        $deploymentDir = File::join(
            $projectDir,
            'deployments'
        );
        $context->set('project_base_dir', $projectDir);
        $context->set('deployment_base_dir', $deploymentDir);
        $locations   = [$projectDir, $deploymentDir];
        $this->info(
            $deployment,
            'Checking workspace exists for project',
            'Locations - ' . implode(', ', $locations)
        );
        $mode = Settings::get('build.chmod.default_folder', Builder::MODE_DEFAULT);
        foreach ($locations as $location) {
            if (is_dir($location)) {
                Log::debug('Build directory exists', [
                    'location' => $location,
                ]);
                continue;
            }
            $this->info(
                $deployment,
                'Creating missing location ' . $location
            );
            Log::debug('Creating build directory', [
                'location' => $location,
                'mode' => $mode,
            ]);
            if (!mkdir($location, $mode, true)) {
                $this->error(
                    $deployment,
                    'Failed creating location ' . $location,
                    [
                        "Location - {$location}",
                        "Mode - {$mode}",
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
                'Created missing location ' . $location,
                [
                    "Location - {$location}",
                    "Mode - {$mode}",
                ]
            );
        }
    }
}