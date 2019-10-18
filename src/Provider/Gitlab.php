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
 * Gitlab source control provider
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Gitlab implements ProviderInterface
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $headUrl     = 'https://gitlab.com/api/v4/projects/{repository}/repository/commits/{branch}';

    /**
     * @var string
     */
    protected $downloadUrl = 'https://gitlab.com/api/v4/projects/{repository}/repository/archive.tar.gz?sha={sha}';

    /**
     * @var string
     */
    protected $configUrl   = 'https://gitlab.com/api/v4/projects/{repository}/repository/files/deploy.yaml?ref={sha}';

    /**
     * @var string
     */
    protected $repoUrl     = 'https://gitlab.com/{repository}';

    /**
     * @var string
     */
    protected $branchUrl   = 'https://gitlab.com/{repository}/tree/{branch}';

    /**
     * @var string
     */
    protected $shaUrl      = 'https://gitlab.com/{repository}/commit/{sha}';

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
     * @see \App\Provider\ProviderInterface::handles()
     */
    public function handles(Project $project)
    {
        return 'gitlab' == $project->provider;
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
            'repository' => $this->encodeRepository($repository),
            'branch'     => $branch,
        ];
        $url = Str::moustaches(
            $this->headUrl,
            $params
        );
        $closure('info', 'Querying Gitlab API for head commit data', "API URL : {$url}");
        $curl = $this->getCurlHandle($url);
        $data = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (200 != $statusCode) {
            $closure(
                'error',
                'Gitlab API request failed',
                implode("\n", [
                    "API URL - {$url}",
                    "CURL Error - (" . curl_errno($curl) . ') ' . curl_error($curl)
                ])
            );
            throw new RuntimeException('Unable to query Gitlab API - ' . $statusCode . ' response code');
        }
        if (!$data = json_decode($data, true)) {
            $closure(
                'error',
                'Unable to parse Gitlab response JSON',
                "API URL : {$url}"
            );
            throw new RuntimeException('Invalid commit data for head');
        }

        return [
            'sha'       => $data['id'],
            'author'    => $data['author_email'],
            'committer' => $data['committer_email'],
            'message'   => $data['title'],
        ];
    }

    /**
     * @see \App\Provider\ProviderInterface::download()
     */
    public function download(Project $project, Deployment $deployment, $directory, Closure $closure = null)
    {
        $params = [
            'repository' => $this->encodeRepository($project->repository),
            'sha'        => $deployment->sha,
        ];
        $url = Str::moustaches(
            $this->downloadUrl,
            $params
        );
        $closure(
            'info',
            'Initiating codebase download using Gitlab provider'
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
        if($statusCode != 200){
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
            'repository' => $this->encodeRepository($project->repository),
            'sha'        => $deployment->sha,
        ];
        $url = Str::moustaches(
            $this->configUrl,
            $params
        );
        $closure(
            'info',
            'Querying Gitlab API for deployment configuration',
            implode("\n", [
                "API URL - {$url}"
            ])
        );
        $curl = $this->getCurlHandle($url);
        if (false === ($json = curl_exec($curl))) {
            $closure(
                'error',
                'Gitlab API request failed',
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
                'Failed to parse Gitlab response json',
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
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_USERAGENT      => 'ronanchilvers/deploy - curl ' . curl_version()['version'],
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_HTTPHEADER     => [
                "Private-Token: {$this->token}"
            ],
        ]);

        return $curl;
    }

    /**
     * Encode a repository name
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function encodeRepository($repository)
    {
        return str_replace('.', '%2E', urlencode($repository));
    }
}
