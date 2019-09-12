<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use App\Builder;
use App\Facades\Log;
use App\Facades\Settings;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Utility\File;

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
        $baseDir = Settings::get('build.base_dir');
        $project = $context->getOrThrow('project', 'Invalid or missing project');
        $key     = $project->id;
        $projectDir = File::join(
            $baseDir,
            $key
        );
        $releaseDir = File::join(
            $projectDir,
            'releases'
        );
        $context->set('project_base_dir', $projectDir);
        $context->set('release_base_dir', $releaseDir);
        $locations   = [$projectDir, $releaseDir];
        $mode = Settings::get('build.chmod.default_folder', Builder::MODE_DEFAULT);
        foreach ($locations as $location) {
            if (is_dir($location)) {
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
                Log::error('Unable to create build directory', [
                    'location' => $location,
                    'mode' => $mode,
                ]);
                throw new BuildException(
                    'Unable to create build directory at ' . $location
                );
            }
        }
    }
}
