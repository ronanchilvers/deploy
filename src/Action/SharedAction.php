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
        $baseDir = $context->get('project_base_dir');
        if (is_null($baseDir)) {
            throw new RuntimeException('Invalid or missing project base directory');
        }
        $folderMode = Settings::get('build.chmod.default_folder', Builder::MODE_DEFAULT);
        $fileMode = Settings::get('build.chmod.default_file', Builder::MODE_DEFAULT_FILE);
        $sharedBaseDir = File::join($baseDir, 'shared');
        if (!is_dir($sharedBaseDir)) {
            Log::debug('Creating shared base directory', [
                'dir' => $sharedBaseDir,
            ]);
            if (!mkdir($sharedBaseDir, $folderMode, true)) {
                throw new RuntimeException('Unable to create shared base directory');
            }
        }
        $releaseDir = $context->get('release_dir');
        if (is_null($releaseDir)) {
            throw new RuntimeException('Invalid or missing release directory');
        }

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
                $thisReleaseDir = File::join($releaseDir, $folder);

                // Create the shared directory if needed
                if (!is_dir($sharedDir)) {
                    if (is_dir($thisReleaseDir)) {
                        if (!File::cp($thisReleaseDir, $sharedDir)) {
                            Log::debug('Unable to copy shared folder from release', [
                                'shared_dir'  => $sharedDir,
                                'release_dir' => $thisReleaseDir,
                            ]);
                            throw new RuntimeException('Unable to move shared folder from release to shared');
                        }
                    } elseif (!mkdir($sharedDir, $folderMode, true)) {
                        Log::debug('Unable to create shared folder', [
                            'shared_dir'  => $sharedDir,
                            'release_dir' => $thisReleaseDir,
                        ]);
                        throw new RuntimeException('Unable to create shared folders');
                    }
                }

                // Remove the shared folder from the release if it exists
                if (!File::rm($thisReleaseDir)) {
                    Log::debug('Unable to remove shared folder from release', [
                        'shared_dir'  => $sharedDir,
                        'release_dir' => $thisReleaseDir,
                    ]);
                    throw new RuntimeException('Unable to remove shared folder from release');
                }

                // Link the shared location into the release
                $parentDir = dirname($thisReleaseDir);
                if (!is_dir($parentDir) && !mkdir($parentDir, $folderMode, true)) {
                    Log::debug('Unable to create parent directory for symlinking', [
                        'parent_dir'  => $parentDir,
                        'shared_dir'  => $sharedDir,
                        'release_dir' => $thisReleaseDir,
                    ]);
                    throw new RuntimeException('Unable to create parent directory for symlinking');
                }
                if (!symlink($sharedDir, $thisReleaseDir)) {
                    Log::debug('Unable to symlink shared folder', [
                        'shared_dir'  => $sharedDir,
                        'release_dir' => $thisReleaseDir,
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
                $thisReleaseFile = File::join($releaseDir, $filename);
                $thisReleaseDir  = dirname($thisReleaseFile);

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
                // Copy over the file from the release if it exists
                if (file_exists($thisReleaseFile)) {
                    Log::debug('Shared file exists in release', [
                        'shared_file'  => $sharedFilename,
                        'release_file' => $thisReleaseFile,
                    ]);
                    if (!file_exists($sharedFilename) && !File::cp($thisReleaseFile, $sharedFilename)) {
                        Log::debug('Unable to copy shared file from release', [
                            'shared_dir'   => $sharedDir,
                            'shared_file'  => $sharedFilename,
                            'release_file' => $thisReleaseFile,
                        ]);
                        throw new RuntimeException('Unable to copy shared file from release');
                    }
                    Log::debug('Removing shared file from release', [
                        'shared_file'  => $sharedFilename,
                        'release_file' => $thisReleaseFile,
                    ]);
                    if (!File::rm($thisReleaseFile)) {
                        Log::debug('Unable to remove shared file from release', [
                            'shared_dir'   => $sharedDir,
                            'shared_file'  => $sharedFilename,
                            'release_file' => $thisReleaseFile,
                        ]);
                        throw new RuntimeException('Unable to remove shared file from release');
                    }
                }

                // Make sure the parent directory exists for the shared file
                if (!is_dir($thisReleaseDir)) {
                    if (!mkdir($thisReleaseDir, $folderMode, true)) {
                        Log::debug('Unable to create parent directory for shared file in release', [
                            'shared_dir'   => $sharedDir,
                            'shared_file'  => $sharedFilename,
                            'release_file' => $thisReleaseFile,
                            'release_dir'  => $thisReleaseDir,
                        ]);
                        throw new RuntimeException('Unable to create parent directory for shared file in release');
                    }
                }

                // Touch the shared file
                if (!file_exists($sharedFilename)) {
                    if (!touch($sharedFilename)) {
                        Log::debug('Unable to touch shared file', [
                            'shared_dir'   => $sharedDir,
                            'shared_file'  => $sharedFilename,
                            'release_file' => $thisReleaseFile,
                            'release_dir'  => $thisReleaseDir,
                        ]);
                        throw new RuntimeException('Unable to touch shared file');
                    }
                    if (!chmod($sharedFilename, $fileMode)) {
                        Log::debug('Unable to chmod shared file', [
                            'shared_dir'   => $sharedDir,
                            'shared_file'  => $sharedFilename,
                            'release_file' => $thisReleaseFile,
                            'release_dir'  => $thisReleaseDir,
                        ]);
                        throw new RuntimeException('Unable to chmod shared file');
                    }
                }

                // Symlink the shared file into place
                if (!symlink($sharedFilename, $thisReleaseFile)) {
                    Log::debug('Unable to symlink shared file', [
                        'shared_dir'   => $sharedDir,
                        'shared_file'  => $sharedFilename,
                        'release_file' => $thisReleaseFile,
                        'release_dir'  => $thisReleaseDir,
                    ]);
                    throw new RuntimeException('Unable to symlink shared file');
                }
            }
        }

    }
}
