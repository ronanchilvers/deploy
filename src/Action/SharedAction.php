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
class SharedAction extends AbstractAction
{
    /**
     * @see \App\Action\ActionInterface::run()
     */
    public function run(Config $configuration, Context $context)
    {
        $deployment    = $context->getOrThrow('deployment', 'Invalid or missing deployment');
        $baseDir       = $context->getOrThrow('project_base_dir', 'Invalid or missing project base directory');
        $folderMode    = Settings::get('build.chmod.default_folder', Builder::MODE_DEFAULT);
        $fileMode      = Settings::get('build.chmod.default_file', Builder::MODE_DEFAULT_FILE);
        $sharedBaseDir = File::join($baseDir, 'shared');
        if (!is_dir($sharedBaseDir)) {
            $this->info(
                $deployment,
                'Creating base shared directory',
                [
                    "Directory - {$sharedBaseDir}",
                ]
            );
            Log::debug('Creating shared base directory', [
                'dir' => $sharedBaseDir,
            ]);
            if (!mkdir($sharedBaseDir, $folderMode, true)) {
                $this->error(
                    $deployment,
                    'Failed creating base shared directory',
                    [
                        "Directory - {$sharedBaseDir}",
                        "Mode - {$folderMode}",
                    ]
                );
                throw new RuntimeException('Unable to create shared base directory');
            }
        }
        $deploymentDir = $context->getOrThrow('deployment_dir', 'Invalid or missing deployment directory');
        // Get the shared items from the configuration object
        $shared = $configuration->get('shared', []);
        if (0 == count($shared)) {
            $this->info(
                $deployment,
                'No shared files or folders set in deployment configuration'
            );
            return;
        }
        // Shared folders
        $sharedFolders = [];
        if (
            isset($shared['folders']) &&
            is_array($shared['folders']) &&
            0 < count($shared['folders'])
        ) {
            $this->info(
                $deployment,
                'Verifying shared folders',
                [
                    "Shared folders - " . implode(", ", $shared['folders']),
                ]
            );
            foreach ($shared['folders'] as $folder) {
                $sharedDir = File::join($sharedBaseDir, $folder);
                $thisDeploymentDir = File::join($deploymentDir, $folder);

                // Create the shared directory if needed
                if (!is_dir($sharedDir)) {
                    // $this->info(
                    //     $deployment,
                    //     'Creating shared folder',
                    //     [
                    //         "Folder - {$sharedDir}",
                    //     ]
                    // );
                    if (is_dir($thisDeploymentDir)) {
                        $this->info(
                            $deployment,
                            'Copying shared folder from deployment',
                            [
                                "Deployment folder - {$thisDeploymentDir}",
                                "Shared folder - {$sharedDir}",
                            ]
                        );
                        if (!File::cp($thisDeploymentDir, $sharedDir)) {
                            $this->error(
                                $deployment,
                                'Failed copying shared folder from deployment',
                                [
                                    "Deployment folder - {$thisDeploymentDir}",
                                    "Shared folder - {$sharedDir}",
                                ]
                            );
                            Log::debug('Unable to copy shared folder from deployment', [
                                'shared_dir'  => $sharedDir,
                                'deployment_dir' => $thisDeploymentDir,
                            ]);
                            throw new RuntimeException('Unable to move shared folder from deployment to shared');
                        }
                    } elseif (!mkdir($sharedDir, $folderMode, true)) {
                        $this->error(
                            $deployment,
                            'Unable to create shared folder',
                            [
                                "Shared folder - {$sharedDir}",
                                "Mode - {$folderMode}",
                            ]
                        );
                        Log::debug('Unable to create shared folder', [
                            'shared_dir'  => $sharedDir,
                            'deployment_dir' => $thisDeploymentDir,
                        ]);
                        throw new RuntimeException('Unable to create shared folders');
                    }
                }

                // Remove the shared folder from the deployment if it exists
                $this->info(
                    $deployment,
                    'Removing shared folder from deployment directory',
                    [
                        "Deployment folder - {$thisDeploymentDir}",
                    ]
                );
                if (!File::rm($thisDeploymentDir)) {
                    $this->info(
                        $deployment,
                        'Shared folder not found in deployment directory',
                        [
                            "Deployment folder - {$thisDeploymentDir}",
                        ]
                    );
                    Log::debug('Shared folder not found in deployment directory', [
                        'shared_dir'     => $sharedDir,
                        'deployment_dir' => $thisDeploymentDir,
                    ]);
                }

                // Link the shared location into the deployment
                $parentDir = dirname($thisDeploymentDir);
                if (!is_dir($parentDir) && !mkdir($parentDir, $folderMode, true)) {
                    $this->error(
                        $deployment,
                        'Unable to create parent directory for symlinking',
                        [
                            "Parent shared folder - {$parentDir}",
                            "Mode - {$folderMode}",
                        ]
                    );
                    Log::debug('Unable to create parent directory for symlinking', [
                        'parent_dir'  => $parentDir,
                        'shared_dir'  => $sharedDir,
                        'deployment_dir' => $thisDeploymentDir,
                    ]);
                    throw new RuntimeException('Unable to create parent directory for symlinking');
                }
                if (!symlink($sharedDir, $thisDeploymentDir)) {
                    $this->error(
                        $deployment,
                        'Unable to symlink shared folder',
                        [
                            "Deployment link - {$thisDeploymentDir}",
                            "Shared folder - {$sharedDir}",
                        ]
                    );
                    Log::debug('Unable to symlink shared folder', [
                        'shared_dir'  => $sharedDir,
                        'deployment_dir' => $thisDeploymentDir,
                    ]);
                    throw new RuntimeException('Unable to symlink shared folder');
                }
                // $this->info(
                //     $deployment,
                //     'Shared folder verified - ' . $sharedDir
                // );
                $sharedFolders[$sharedDir] = $sharedDir;
            }
        }
        // Shared files
        if (
            isset($shared['files']) &&
            is_array($shared['files']) &&
            0 < count($shared['files'])
        ) {
            $this->info(
                $deployment,
                'Verifying shared files',
                [
                    "Shared files - " . implode(", ", $shared['files']),
                ]
            );
            foreach ($shared['files'] as $filename) {
                $sharedFilename     = File::join($sharedBaseDir, $filename);
                $sharedDir          = dirname($sharedFilename);
                $thisDeploymentFile = File::join($deploymentDir, $filename);
                $thisDeploymentDir  = dirname($thisDeploymentFile);

                // Check that we're not sharing the parent directory already
                if (isset($sharedFolders[$sharedDir])) {
                    $this->error(
                        $deployment,
                        'Shared file parent folder is already shared',
                        [
                            "Shared file - {$sharedFilename}",
                            "Shared folder - {$sharedDir}",
                        ]
                    );
                    Log::error('Parent folder is already shared', [
                        'shared_dir' => $sharedDir,
                        'shared_file' => $sharedFilename,
                    ]);
                    throw new RuntimeException('Parent folder for shared file is already shared');
                }

                // Create the shared directory if needed
                if (!is_dir($sharedDir)) {
                    $this->info(
                        $deployment,
                        'Creating parent folder for shared file',
                        [
                            "Shared file - {$sharedFilename}",
                            "Shared folder - {$sharedDir}",
                        ]
                    );
                    if (!mkdir($sharedDir, $folderMode, true)) {
                        $this->error(
                            $deployment,
                            'Unable to create parent folder for shared file',
                            [
                                "Shared file - {$sharedFilename}",
                                "Shared folder - {$sharedDir}",
                            ]
                        );
                        Log::debug('Unable to create parent folder for shared file', [
                            'shared_dir'  => $sharedDir,
                            'shared_file' => $sharedFilename,
                        ]);
                        throw new RuntimeException('Unable to create parent folder for shared file');
                    }
                }
                // Copy over the file from the deployment if it exists
                if (file_exists($thisDeploymentFile)) {
                    $this->info(
                        $deployment,
                        'Copying shared file from deployment into shared folder',
                        [
                            "Deployment file - {$thisDeploymentFile}",
                            "Shared file - {$sharedFilename}",
                            "Shared folder - {$sharedDir}",
                        ]
                    );
                    Log::debug('Shared file exists in deployment', [
                        'shared_file'  => $sharedFilename,
                        'deployment_file' => $thisDeploymentFile,
                    ]);
                    if (!file_exists($sharedFilename) && !File::cp($thisDeploymentFile, $sharedFilename)) {
                        $this->error(
                            $deployment,
                            'Unable to copy shared file from deployment',
                            [
                                "Deployment file - {$thisDeploymentFile}",
                                "Shared file - {$sharedFilename}",
                                "Shared folder - {$sharedDir}",
                            ]
                        );
                        Log::debug('Unable to copy shared file from deployment', [
                            'shared_dir'   => $sharedDir,
                            'shared_file'  => $sharedFilename,
                            'deployment_file' => $thisDeploymentFile,
                        ]);
                        throw new RuntimeException('Unable to copy shared file from deployment');
                    }
                    $this->info(
                        $deployment,
                        'Removing shared file from deployment',
                        [
                            "Deployment file - {$thisDeploymentFile}",
                            "Shared file - {$sharedFilename}",
                            "Shared folder - {$sharedDir}",
                        ]
                    );
                    Log::debug('Removing shared file from deployment', [
                        'shared_file'  => $sharedFilename,
                        'deployment_file' => $thisDeploymentFile,
                    ]);
                    if (!File::rm($thisDeploymentFile)) {
                        $this->error(
                            $deployment,
                            'Unable to remove shared file from deployment',
                            [
                                "Deployment file - {$thisDeploymentFile}",
                                "Shared file - {$sharedFilename}",
                                "Shared folder - {$sharedDir}",
                            ]
                        );
                        Log::debug('Unable to remove shared file from deployment', [
                            'shared_dir'   => $sharedDir,
                            'shared_file'  => $sharedFilename,
                            'deployment_file' => $thisDeploymentFile,
                        ]);
                        throw new RuntimeException('Unable to remove shared file from deployment');
                    }
                }

                // Make sure the parent directory exists for the shared file
                if (!is_dir($thisDeploymentDir)) {
                    $this->info(
                        $deployment,
                        'Creating parent folder for deployment link',
                        [
                            "Deployment file - {$thisDeploymentFile}",
                            "Mode - {$folderMode}",
                        ]
                    );
                    if (!mkdir($thisDeploymentDir, $folderMode, true)) {
                        $this->info(
                            $deployment,
                            'Unable to create parent folder for shared file in deployment',
                            [
                                "Deployment file - {$thisDeploymentFile}",
                                "Mode - {$folderMode}",
                            ]
                        );
                        Log::debug('Unable to create parent directory for shared file in deployment', [
                            'shared_dir'   => $sharedDir,
                            'shared_file'  => $sharedFilename,
                            'deployment_file' => $thisDeploymentFile,
                            'deployment_dir'  => $thisDeploymentDir,
                        ]);
                        throw new RuntimeException('Unable to create parent directory for shared file in deployment');
                    }
                }

                // Touch the shared file
                if (!file_exists($sharedFilename)) {
                    $this->info(
                        $deployment,
                        'Creating empty shared file for symlinking',
                        [
                            "Shared file - {$sharedFilename}",
                        ]
                    );
                    if (!touch($sharedFilename)) {
                        $this->error(
                            $deployment,
                            'Unable to create shared file for symlinking',
                            [
                                "Shared file - {$sharedFilename}",
                            ]
                        );
                        Log::debug('Unable to touch shared file', [
                            'shared_dir'   => $sharedDir,
                            'shared_file'  => $sharedFilename,
                            'deployment_file' => $thisDeploymentFile,
                            'deployment_dir'  => $thisDeploymentDir,
                        ]);
                        throw new RuntimeException('Unable to touch shared file');
                    }
                    if (!chmod($sharedFilename, $fileMode)) {
                        $this->error(
                            $deployment,
                            'Unable to chmod shared file',
                            [
                                "Shared file - {$sharedFilename}",
                                "Mode - {$fileMode}",
                            ]
                        );
                        Log::debug('Unable to chmod shared file', [
                            'shared_dir'   => $sharedDir,
                            'shared_file'  => $sharedFilename,
                            'deployment_file' => $thisDeploymentFile,
                            'deployment_dir'  => $thisDeploymentDir,
                        ]);
                        throw new RuntimeException('Unable to chmod shared file');
                    }
                }

                // Symlink the shared file into place

                if (!symlink($sharedFilename, $thisDeploymentFile)) {
                    $this->error(
                        $deployment,
                        'Unable to symlink shared file',
                        [
                            "Shared file - {$sharedFilename}",
                            "Deployment link - {$thisDeploymentFile}",
                        ]
                    );
                    Log::debug('Unable to symlink shared file', [
                        'shared_dir'   => $sharedDir,
                        'shared_file'  => $sharedFilename,
                        'deployment_file' => $thisDeploymentFile,
                        'deployment_dir'  => $thisDeploymentDir,
                    ]);
                    throw new RuntimeException('Unable to symlink shared file');
                }
                $this->info(
                    $deployment,
                    'Symlinked shared file into deployment',
                    [
                        "Shared file - {$sharedFilename}",
                        "Deployment link - {$thisDeploymentFile}",
                    ]
                );
            }
        }
    }
}
