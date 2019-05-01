<?php

namespace App;

use App\Builder\BuildException;
use App\Facades\Log;
use App\Facades\Settings;
use App\Model\Project;
use App\Model\Release;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

/**
 * The builder is responsible for building a new release from a given repository
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Builder
{
    const MODE_DEFAULT       = 0770;
    const MODE_WRITABLE_FILE = 0660;
    const MODE_WRITABLE_DIR  = 0770;

    /**
     * @var string
     */
    protected $gitPath;

    /**
     * @var string
     */
    protected $composerPath;

    /**
     * @var array
     */
    protected $gitCommands = [
        'clone'    => 'clone --progress --recursive %url% %dir% --branch %localbranch%',
        'prepare'  => 'submodule update --init --recursive',
        'checkout' => 'checkout -q -f %localbranch%',
        'reset'    => 'reset --hard %revision%',
    ];

    /**
     * The base directory for all deployments
     *
     * Probably something like '/var/www'
     *
     * @var string
     */
    protected $baseDir;

    /**
     * The base directory for a project
     *
     * Something like '/var/www/foobar'
     *
     * @var string
     */
    protected $projectDir;

    /**
     * The directory in which shared files and folders are stored
     *
     * @var string
     */
    protected $sharedDir;

    /**
     * The directory in which the current project release is kept
     *
     * Something like '/var/www/foobar/releases/8271h09dhe'
     *
     * @var string
     */
    protected $releaseDir;

    /**
     * @var string
     */
    protected $cloneDir;

    /**
     * The symlink to the current live release
     *
     * Something like '/var/www/foobar/current'
     *
     * @var string
     */
    protected $productionDir;

    /**
     * @var App\Model\Project
     */
    protected $project;

    /**
     * @var Release
     */
    protected $release;

    /**
     * @var array
     */
    protected $config;

    /**
     * Class constructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(
        $baseDir,
        Project $project,
        $gitPath = null,
        $composerPath = null
    ) {
        if (is_null($gitPath)) {
            $gitPath = trim(exec('/usr/bin/which git'));
        }
        if (is_null($composerPath)) {
            $composerPath = trim(exec('/usr/bin/which composer'));
        }

        $this->project      = $project;
        $this->gitPath      = $gitPath;
        $this->composerPath = $composerPath;
        $this->baseDir      = $baseDir;
        $this->projectDir   = $baseDir . '/' . $project->id;
        $this->sharedDir    = $this->projectDir . '/shared';
        $this->releaseDir   = $this->projectDir . '/releases';
        $this->config       = [];
    }

    /**
     * Scan the remote repository for a YAML configuration file
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function scan()
    {
        Log::debug(sprintf('In %s:%d', __METHOD__, __LINE__));
        $config = $this->project->getDeployConfig();
        if (is_string($config)) {
            $this->config = Yaml::parse($config);
        }
    }

    /**
     * Initialise the release
     *
     * - prepare the filesystem if required
     * - create the release record
     * - checkout the codebase
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function initialise()
    {
        Log::debug(sprintf('In %s:%d', __METHOD__, __LINE__));
        // Prepare the filesystem for deployment
        $this->prepareBuildDirectory();

        // Create the new release
        $release = Release::where(['project' => $this->project->id])
            ->orderBy('number', 'desc')
            ->first();
        if (!$release instanceof Release) {
            $release = new Release([
                'project' => $this->project->id,
                'number'  => 0
            ]);
        } else {
            $release = $release->replicate();
        }
        $release->number += 1;
        if (!$release->save()) {
            throw new BuildException('Unable to create new release record');
        }
        $this->release = $release;
        Log::debug('Created new release', [
            'project' => $this->project->id,
            'project_name' => $this->project->name,
            'release' => $release->id,
            'release_number' => $release->number,
        ]);

        // Checkout the codebase
        $this->cloneDir = $this->releaseDir . '/' . $this->release->number;
        Log::info('Cloning into release directory', [
            'project' => $this->project->id,
            'project_name' => $this->project->name,
            'release' => $this->release->id,
            'release_number' => $this->release->number,
            'clone_dir' => $this->cloneDir,
        ]);
        $command = $this->getGitCommand(
            'clone',
            [ '%dir%' => $this->cloneDir ]
        );
        $this->execute(
            $command,
            sprintf('Unable to clone project %s (%d)', $this->project->name, $this->project->id)
        );

        $this->notify(
            $this->config('initialise.notify', null),
            'Project initialised'
        );
    }

    /**
     * Prepare the release
     *
     * - composer install
     * - shared files and folders
     * - writable files and folders
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function prepare()
    {
        Log::debug(sprintf('In %s:%d', __METHOD__, __LINE__));
        Log::debug('Checking out codebase branch', [
            'project' => $this->project->id,
            'project_name' => $this->project->name,
            'project_branch' => $this->project->branch,
            'release' => $this->release->id,
            'release_number' => $this->release->number,
            'clone_dir' => $this->cloneDir,
        ]);
        $this->execute(
            $this->getGitCommand(
                'checkout',
                [ '%dir%' => $this->cloneDir ]
            ),
            sprintf('Unable to prepare project %s (%d)', $this->project->name, $this->project->id),
            $this->cloneDir
        );

        if (false == $this->config('prepare.composer', true)) {
            Log::debug('Checking for composer.json', [
                'project' => $this->project->id,
                'project_name' => $this->project->name,
                'release' => $release->id,
                'release_number' => $release->number,
            ]);
            if (file_exists($this->cloneDir . '/composer.json')) {
                Log::notice('Composer.json found - running composer', [
                    'project' => $this->project->id,
                    'project_name' => $this->project->name,
                    'release' => $release->id,
                    'release_number' => $release->number,
                ]);
                $this->execute(
                    $this->composerPath . ' install --no-interaction --no-dev --optimize-autoloader',
                    sprintf('Unable to run composer for new release'),
                    $this->cloneDir
                );
            } else {
                Log::notice('Composer.json not found - composer disabled', [
                    'project' => $this->project->id,
                    'project_name' => $this->project->name,
                    'release' => $release->id,
                    'release_number' => $release->number,
                ]);
            }
        } else {
            Log::debug('Composer support disabled by config', [
                'project' => $this->project->id,
                'project_name' => $this->project->name,
                'release' => $release->id,
                'release_number' => $release->number,
            ]);
        }

        $this->notify(
            $this->config('prepare.notify', null),
            'Project preparation completed'
        );
    }

    /**
     * Finailise the release
     *
     * - symlink the new release
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function finalise()
    {
        Log::debug(sprintf('In %s:%d', __METHOD__, __LINE__));
        // $this->release->status = 'complete';
        // if (!$this->release->save()) {
        //     throw new BuildException('Unable to mark release as complete');
        // }

        $this->notify(
            $this->config('finalise.notify', null),
            'Deployment completed'
        );
    }

    /**
     * Prepare the build directory
     *
     * This method ensures that the build directory that we are deploying to
     * has all the required base directories. It creates a structure like this:
     *
     * %baseDir%/
     *      releases/
     *      shared/
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function prepareBuildDirectory()
    {
        Log::debug(sprintf('In %s:%d', __METHOD__, __LINE__));
        $locations = [
            $this->projectDir,
            $this->releaseDir,
            $this->sharedDir
        ];
        $mode = Settings::get('build.chmod.default', static::MODE_DEFAULT);
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

    /**
     * Get a git command line string
     *
     * @param string $command
     * @param string $url
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getGitCommand($key, $data = [])
    {
        if (!isset($this->gitCommands[$key])) {
            throw new BuildException('Invalid git command ' . $key);
        }
        $data = array_merge([
            '%dir%' => escapeshellarg($this->cloneDir),
            '%branch%' => escapeshellarg('origin/' . $this->project->branch),
            '%localbranch%' => escapeshellarg($this->project->branch),
            '%url%' => escapeshellarg($this->project->getCloneUrl()),
        ], $data);
        $command = $this->gitCommands[$key];
        $command = str_replace(array_keys($data), array_values($data), $command);
        Log::debug('Compiled git command', [
            'key'     => $key,
            'data'    => $data,
            'command' => $command,
        ]);
        return $this->gitPath . ' ' . $command;
    }

    /**
     * Execute a cli command
     *
     * @param string $command
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function execute($command, $message, $workingDir = null)
    {
        if (is_null($workingDir)) {
            $workingDir = $this->baseDir;
        }
        Log::debug('Running command', [
            'command' => $command,
            'message' => $message
        ]);
        $process = new Process($command, $workingDir);
        $process->setTimeout(3600);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new BuildException($message);
        }
        Log::debug('Command completed', [
            'command' => $command,
            'message' => $message
        ]);

        return $process;
    }

    /**
     * Execute notifications
     *
     * @param array $notifiers
     * @param string $message
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function notify($notifiers, string $message)
    {
        if (is_null($notifiers)) {
            return;
        }
        $defaultNotifiers = $this->config('notify', []);
        if (is_string($notifiers)) {
            $notifiers = [$notifiers];
        }
        foreach ($notifiers as $notifier) {
            if (is_string($notifier) && isset($defaultNotifiers[$notifier])) {
                $notifier = $defaultNotifiers[$notifier];
            }
            if (!is_array($notifier)) {
                $notifier = [
                    'type' => 'email',
                    'to'   => $notifier,
                ];
            }
            Log::debug('Notifying', [
                'notifier' => $notifier,
                'message' => $message,
            ]);
        }
    }

    /**
     * Get a config value with a default
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function config($key, $default = null)
    {
        if (false === strpos($key, '.')) {
            if (isset($this->config[$key])) {
                return $this->config[$key];
            }
            return $default;
        }
        $subkeys = explode('.', $key);
        $value   = $this->config;
        foreach ($subkeys as $subkey) {
            if (!isset($value[$subkey])) {
                return $default;
            }
            $value = $value[$subkey];
        }

        return $value;
    }
}
