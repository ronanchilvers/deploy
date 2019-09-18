<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use App\Action\Traits\IsInitialiseStage;
use App\Builder;
use App\Facades\Log;
use App\Facades\Settings;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Utility\File;
use RuntimeException;

/**
 * Action to manage any shared locations for a project
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class SharedAction extends AbstractAction implements ActionInterface
{
    /**
     * @see App\Action\ActionInterface::run()
     */
    public function run(Config $configuration, Context $context)
    {
        $baseDir       = $context->getOrThrow('project_base_dir', 'Invalid or missing project base directory');
        $folderMode    = Settings::get('build.chmod.default_folder', Builder::MODE_DEFAULT);
        $fileMode      = Settings::get('build.chmod.default_file', Builder::MODE_DEFAULT_FILE);
        $sharedBaseDir = File::join($baseDir, 'shared');
        if (!is_dir($sharedBaseDir)) {
            Log::debug('Creating shared base directory', [
                'dir' => $sharedBaseDir,
            ]);
            if (!mkdir($sharedBaseDir, $folderMode, true)) {
                throw new RuntimeException('Unable to create shared base directory');
            }
        }
        $deploymentDir = $context->getOrThrow('deployment_dir', 'Invalid or missing deployment directory');
        // Get the shared items from the configuration object
        $shared     = $configuration->get('shared', []);
        if (0 == count($shared)) {
            return;
        }
        // Shared folders
        $sharedFolders = [];
        if (
            isset($shared['folders']) &&
            is_array($shared['folders']) &&
            0 < count($shared['folders'])
        ) {
            foreach ($shared['folders'] as $folder) {
                $sharedDir      = File::join($sharedBaseDir, $folder);
                $thisdeploymentDir = File::join($deploymentDir, $folder);

                // Create the shared directory if needed
                if (!is_dir($sharedDir)) {
                    if (is_dir($thisdeploymentDir)) {
                        if (!File::cp($thisdeploymentDir, $sharedDir)) {
                            Log::debug('Unable to copy shared folder from deployment', [
                                'shared_dir'  => $sharedDir,
                                'deployment_dir' => $thisdeploymentDir,
                            ]);
                            throw new RuntimeException('Unable to move shared folder from deployment to shared');
                        }
                    } elseif (!mkdir($sharedDir, $folderMode, true)) {
                        Log::debug('Unable to create shared folder', [
                            'shared_dir'  => $sharedDir,
                            'deployment_dir' => $thisdeploymentDir,
                        ]);
                        throw new RuntimeException('Unable to create shared folders');
                    }
                }

                // Remove the shared folder from the deployment if it exists
                if (!File::rm($thisdeploymentDir)) {
                    Log::debug('Unable to remove shared folder from deployment', [
                        'shared_dir'  => $sharedDir,
                        'deployment_dir' => $thisdeploymentDir,
                    ]);
                    throw new RuntimeException('Unable to remove shared folder from deployment');
                }

                // Link the shared location into the deployment
                $parentDir = dirname($thisdeploymentDir);
                if (!is_dir($parentDir) && !mkdir($parentDir, $folderMode, true)) {
                    Log::debug('Unable to create parent directory for symlinking', [
                        'parent_dir'  => $parentDir,
                        'shared_dir'  => $sharedDir,
                        'deployment_dir' => $thisdeploymentDir,
                    ]);
                    throw new RuntimeException('Unable to create parent directory for symlinking');
                }
                if (!symlink($sharedDir, $thisdeploymentDir)) {
                    Log::debug('Unable to symlink shared folder', [
                        'shared_dir'  => $sharedDir,
                        'deployment_dir' => $thisdeploymentDir,
                    ]);
                    throw new RuntimeException('Unable to symlink shared folder');
                }
                $sharedFolders[$sharedDir] = $sharedDir;
            }
        }
        // Shared files
        if (
            isset($shared['files']) &&
            is_array($shared['files']) &&
            0 < count($shared['files'])
        ) {
            foreach ($shared['files'] as $filename) {
                $sharedFilename  = File::join($sharedBaseDir, $filename);
                $sharedDir       = dirname($sharedFilename);
                $thisdeploymentFile = File::join($deploymentDir, $filename);
                $thisdeploymentDir  = dirname($thisdeploymentFile);

                // Check that we're not sharing the parent directory already
                if (isset($sharedFolders[$sharedDir])) {
                    Log::error('Parent folder is already shared', [
                        'shared_dir' => $sharedDir,
                        'shared_file' => $sharedFilename,
                    ]);
                    throw new RuntimeException('Parent folder for shared file is already shared');
                }

                // Create the shared directory if needed
                if (!is_dir($sharedDir)) {
                    if (!mkdir($sharedDir, $folderMode, true)) {
                        Log::debug('Unable to create parent folder for shared file', [
                            'shared_dir'  => $sharedDir,
                            'shared_file' => $sharedFilename,
                        ]);
                        throw new RuntimeException('Unable to create parent folder for shared file');
                    }
                }
                // Copy over the file from the deployment if it exists
                if (file_exists($thisdeploymentFile)) {
                    Log::debug('Shared file exists in deployment', [
                        'shared_file'  => $sharedFilename,
                        'deployment_file' => $thisdeploymentFile,
                    ]);
                    if (!file_exists($sharedFilename) && !File::cp($thisdeploymentFile, $sharedFilename)) {
                        Log::debug('Unable to copy shared file from deployment', [
                            'shared_dir'   => $sharedDir,
                            'shared_file'  => $sharedFilename,
                            'deployment_file' => $thisdeploymentFile,
                        ]);
                        throw new RuntimeException('Unable to copy shared file from deployment');
                    }
                    Log::debug('Removing shared file from deployment', [
                        'shared_file'  => $sharedFilename,
                        'deployment_file' => $thisdeploymentFile,
                    ]);
                    if (!File::rm($thisdeploymentFile)) {
                        Log::debug('Unable to remove shared file from deployment', [
                            'shared_dir'   => $sharedDir,
                            'shared_file'  => $sharedFilename,
                            'deployment_file' => $thisdeploymentFile,
                        ]);
                        throw new RuntimeException('Unable to remove shared file from deployment');
                    }
                }

                // Make sure the parent directory exists for the shared file
                if (!is_dir($thisdeploymentDir)) {
                    if (!mkdir($thisdeploymentDir, $folderMode, true)) {
                        Log::debug('Unable to create parent directory for shared file in deployment', [
                            'shared_dir'   => $sharedDir,
                            'shared_file'  => $sharedFilename,
                            'deployment_file' => $thisdeploymentFile,
                            'deployment_dir'  => $thisdeploymentDir,
                        ]);
                        throw new RuntimeException('Unable to create parent directory for shared file in deployment');
                    }
                }

                // Touch the shared file
                if (!file_exists($sharedFilename)) {
                    if (!touch($sharedFilename)) {
                        Log::debug('Unable to touch shared file', [
                            'shared_dir'   => $sharedDir,
                            'shared_file'  => $sharedFilename,
                            'deployment_file' => $thisdeploymentFile,
                            'deployment_dir'  => $thisdeploymentDir,
                        ]);
                        throw new RuntimeException('Unable to touch shared file');
                    }
                    if (!chmod($sharedFilename, $fileMode)) {
                        Log::debug('Unable to chmod shared file', [
                            'shared_dir'   => $sharedDir,
                            'shared_file'  => $sharedFilename,
                            'deployment_file' => $thisdeploymentFile,
                            'deployment_dir'  => $thisdeploymentDir,
                        ]);
                        throw new RuntimeException('Unable to chmod shared file');
                    }
                }

                // Symlink the shared file into place
                if (!symlink($sharedFilename, $thisdeploymentFile)) {
                    Log::debug('Unable to symlink shared file', [
                        'shared_dir'   => $sharedDir,
                        'shared_file'  => $sharedFilename,
                        'deployment_file' => $thisdeploymentFile,
                        'deployment_dir'  => $thisdeploymentDir,
                    ]);
                    throw new RuntimeException('Unable to symlink shared file');
                }
            }
        }
    }
}
