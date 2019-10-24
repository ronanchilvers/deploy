<?php

namespace App\Provider;

use App\Builder;
use App\Facades\Log;
use App\Facades\Settings;
use App\Model\Deployment;
use App\Model\Project;
use App\Provider\ProviderInterface;
use Closure;
use Exception;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Utility\Str;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

/**
 * Github source control provider
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Github implements ProviderInterface
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $headUrl = 'https://api.github.com/repos/{repository}/git/refs/heads/{branch}';

    /**
     * @var string
     */
    protected $commitUrl = 'https://api.github.com/repos/{repository}/commits/{sha}';

    /**
     * @var string
     */
    protected $downloadUrl = 'https://api.github.com/repos/{repository}/tarball/{sha}';

    /**
     * @var string
     */
    protected $configUrl = 'https://api.github.com/repos/{repository}/contents/deploy.yaml?ref={sha}';

    /**
     * @var string
     */
    protected $repoUrl = 'https://github.com/{repository}';

    /**
     * @var string
     */
    protected $branchUrl = 'https://github.com/{repository}/tree/{branch}';

    /**
     * @var string
     */
    protected $shaUrl = 'https://github.com/{repository}/commit/{sha}';

    /**
     * Class constructor
     *
     * @param string $token
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getLabel()
    {
        return 'Github';
    }

    /**
     * @see \App\Provider\ProviderInterface::handles()
     */
    public function handles(Project $project)
    {
        return 'github' == $project->provider;
    }

    /**
     * Get a repository link for a given repository
     *
     * @param string $repository
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getRepositoryLink(string $repository)
    {
        $params = [
            'repository' => $repository,
        ];

        return Str::moustaches(
            $this->repoUrl,
            $params
        );
    }

    /**
     * Get a link to a repository branch
     *
     * @param string $repository
     * @param string $branch
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getBranchLink(string $repository, string $branch)
    {
        $params = [
            'repository' => $repository,
            'branch'     => $branch,
        ];

        return Str::moustaches(
            $this->branchUrl,
            $params
        );
    }

    /**
     * Get a link for a given repository and sha
     *
     * @param string $repository
     * @param string $sha
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getShaLink(string $repository, string $sha)
    {
        $params = [
            'repository' => $repository,
            'sha'        => $sha,
        ];

        return Str::moustaches(
            $this->shaUrl,
            $params
        );
    }

    /**
     * @see \App\Provider\ProviderInterface::getHeadInfo()
     */
    public function getHeadInfo(string $repository, string $branch, Closure $closure = null)
    {
        $params = [
            'repository' => $repository,
            'branch'     => $branch,
        ];
        $url = Str::moustaches(
            $this->headUrl,
            $params
        );
        $closure('info', 'Querying Github API for head commit data', "API URL : {$url}");
        $curl = $this->getCurlHandle($url);
        if (false === ($data = curl_exec($curl))) {
            $closure(
                'error',
                'Github API request failed',
                implode("\n", [
                    "API URL - {$url}",
                    "CURL Error - (" . curl_errno($curl) . ') ' . curl_error($curl)
                ])
            );
            throw new RuntimeException('CURL request failed - (' . curl_errno($curl) . ') ' . curl_error($curl));
        }
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($statusCode != 200) {
            $error = 'Unknown';
            if (is_string($data)) {
                $data  = json_decode($data, true);
                $error = $data['message'];
            }
            $closure(
                'error',
                'Error obtaining head info from Github',
                implode("\n", [
                    "URL - {$url}",
                    "Status code - {$statusCode}",
                    "Error - {$error}"
                ])
            );
            throw new RuntimeException('Github request failed - ' . $data['message']);
        }
        if (!$data = json_decode($data, true)) {
            $closure(
                'error',
                'Unable to parse Github response JSON',
                "API URL : {$url}"
            );
            throw new RuntimeException('Invalid commit data for head');
        }
        $params['sha'] = $data['object']['sha'];
        $url = Str::moustaches(
            $this->commitUrl,
            $params
        );
        $closure('info', 'Querying Github API for commit detail', "API URL : {$url}");
        $curl = $this->getCurlHandle($url);
        if (false === ($data = curl_exec($curl))) {
            $closure(
                'error',
                'Github API request failed',
                implode("\n", [
                    "API URL - {$url}",
                    "CURL Error - (" . curl_errno($curl) . ') ' . curl_error($curl)
                ])
            );
            throw new RuntimeException('CURL request failed - (' . curl_errno($curl) . ') ' . curl_error($curl));
        }
        curl_close($curl);
        if (!$data = json_decode($data, true)) {
            $closure(
                'error',
                'Unable to parse Github Response JSON',
                implode("\n", [
                    "API URL - {$url}"
                ])
            );
            throw new RuntimeException('Invalid commit data for ' . $params['sha']);
        }

        return [
            'sha'       => $data['sha'],
            'author'    => $data['commit']['author']['email'],
            'committer' => $data['commit']['committer']['email'],
            'message'   => $data['commit']['message'],
        ];
    }

    /**
     * @see \App\Provider\ProviderInterface::download()
     */
    public function download(Project $project, Deployment $deployment, $directory, Closure $closure = null)
    {
        $params = [
            'repository' => $project->repository,
            'sha'        => $deployment->sha,
        ];
        $url = Str::moustaches(
            $this->downloadUrl,
            $params
        );
        $closure(
            'info',
            'Initiating codebase download using Github provider'
        );

        // Download the code tarball
        $filename = tempnam('/tmp', 'deploy-' . $params['sha'] . '-');
        if (!$handle = fopen($filename, "w")) {
            $closure(
                'error',
                'Unable to open temporary download file',
                implode("\n", [
                    "Temporary filename - {$filename}"
                ])
            );
            throw new RuntimeException('Unable to open temporary file');
        }
        $curl = $this->getCurlHandle($url);
        curl_setopt_array($curl, [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_FILE           => $handle
        ]);
        if (false === curl_exec($curl)) {
            $closure(
                'error',
                'Error downloading codebase',
                implode("\n", [
                    "Filename - {$filename}",
                    "CURL Error - (" . curl_errno($curl) . ') ' . curl_error($curl),
                ])
            );
            throw new RuntimeException(curl_errno($curl) . ' - ' . curl_error($curl));
        }
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        fclose($handle);
        if ($statusCode != 200) {
            $closure(
                'error',
                'Error downloading codebase',
                implode("\n", [
                    "Filename - {$filename}",
                    "Status code - {$statusCode}",
                ])
            );
            throw new RuntimeException('Failed to download codebase - ' . $statusCode);
        }

        // Make sure the deployment download directory exists
        if (!is_dir($directory)) {
            $mode = Settings::get('build.chmod.default_folder', Builder::MODE_DEFAULT);
            $closure(
                'info',
                'Creating deployment directory',
                implode("\n", [
                    "Directory - {$directory}",
                ])
            );
            if (!mkdir($directory, $mode, true)) {
                $closure(
                    'error',
                    'Failed to create deployment directory',
                    implode("\n", [
                        "Directory - {$directory}",
                    ])
                );
                throw new RuntimeException(
                    'Unable to create build directory at ' . $directory
                );
            }
        }

        // Decompress the archive into the download directory
        $tar     = Settings::get('binary.tar', '/bin/tar');
        $command = "{$tar} --strip-components=1 -xzf {$filename} -C {$directory}";
        $closure(
            'info',
            'Unpacking codebase tarball',
            implode("\n", [
                "Command - {$command}",
            ])
        );
        $process = new Process(explode(' ', $command));
        $process->run();
        if (!$process->isSuccessful()) {
            $closure(
                'error',
                'Failed to unpack codebase tarball',
                implode("\n", [
                    "Command - {$command}",
                    $process->getErrorOutput(),
                ])
            );
            throw new ProcessFailedException($process);
        }

        // Remove the downloaded archive
        if (!unlink($filename)) {
            $closure(
                'error',
                'Codebase tarball unpacked',
                implode("\n", [
                    $process->getOutput(),
                ])
            );
            throw new RuntimeException('Unable to remove local code archive');
        }
        $closure(
            'info',
            'Codebase download completed'
        );

        return true;
    }

    /**
     * @see \App\Provider\ProviderInterface::scanConfiguration()
     */
    public function scanConfiguration(Project $project, Deployment $deployment, Closure $closure = null)
    {
        $params = [
            'repository' => $project->repository,
            'sha'        => $deployment->sha,
        ];
        $url = Str::moustaches(
            $this->configUrl,
            $params
        );
        $closure(
            'info',
            'Querying Github API for deployment configuration',
            implode("\n", [
                "API URL - {$url}"
            ])
        );
        $curl = $this->getCurlHandle($url);
        $json = curl_exec($curl);
        if (false === $json || !is_string($json)) {
            $closure(
                'error',
                'Github API request failed',
                implode("\n", [
                    "API URL - {$url}",
                    "CURL Error - (" . curl_errno($curl) . ') ' . curl_error($curl)
                ])
            );
            throw new RuntimeException("(" . curl_errno($curl) . ') ' . curl_error($curl));
        }
        $info = curl_getinfo($curl);
        if (404 == $info['http_code']) {
            $closure(
                'info',
                'No deployment configuration found in repository - using defaults'
            );
            Log::debug('Remote configuration file not found', [
                'project' => $project->toArray(),
            ]);
            return;
        }
        $data = json_decode($json, true);
        if (!$data || !isset($data['content'])) {
            $closure(
                'error',
                'Failed to parse Github response json',
                implode("\n", [
                    "API URL - {$url}",
                    "JSON - " . $json
                ])
            );
            Log::debug('Remote configuration file could not be read', [
                'project' => $project->toArray(),
            ]);
            return;
        }
        $yaml = base64_decode($data['content']);
        try {
            $yaml = Yaml::parse($yaml);
            $closure(
                'info',
                'Parsed YAML deployment configuration successfully',
                implode("\n", [
                    "API URL - {$url}",
                    "JSON - " . $json
                ])
            );
        } catch (Exception $ex) {
            $closure(
                'info',
                'Unable to parse YAML deployment configuration',
                implode("\n", [
                    "API URL - {$url}",
                    "Exception - " . $ex->getMessage(),
                ])
            );
            Log::error('Unable to parse YAML deployment configuration', [
                'project'   => $project->toArray(),
                'exception' => $ex,
            ]);
            return;
        }

        return new Config($yaml);
    }

    /**
     * Get a curl handle
     *
     * @param string $url
     * @return resource
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getCurlHandle($url)
    {
        if (!$curl = curl_init($url)) {
            throw new RuntimeException('Unable to initialise CURL Github API request');
        }
        curl_setopt_array($curl, [
            CURLOPT_USERAGENT      => 'ronanchilvers/deploy - curl ' . curl_version()['version'],
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_HTTPHEADER     => [
                "Authorization: token {$this->token}"
            ],
        ]);

        return $curl;
    }
}
