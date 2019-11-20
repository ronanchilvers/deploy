<?php

namespace App\Provider;

use App\Builder;
use App\Facades\Log;
use App\Facades\Settings;
use App\Model\Deployment;
use App\Model\Project;
use App\Provider\AbstractProvider;
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
class Gitlab extends AbstractProvider implements ProviderInterface
{
    /**
     * @var array
     */
    protected $typesHandled = ['gitlab'];

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $headUrl = 'https://gitlab.com/api/v4/projects/{repository}/repository/commits/{branch}';

    /**
     * @var string
     */
    protected $downloadUrl = 'https://gitlab.com/api/v4/projects/{repository}/repository/archive.tar.gz?sha={sha}';

    /**
     * @var string
     */
    protected $configUrl = 'https://gitlab.com/api/v4/projects/{repository}/repository/files/deploy.yaml?ref={sha}';

    /**
     * @var string
     */
    protected $repoUrl = 'https://gitlab.com/{repository}';

    /**
     * @var string
     */
    protected $branchUrl = 'https://gitlab.com/{repository}/tree/{branch}';

    /**
     * @var string
     */
    protected $shaUrl = 'https://gitlab.com/{repository}/commit/{sha}';

    /**
     * @see \App\Provider\ProviderInterface::getHeadInfo()
     */
    public function getHeadInfo(string $repository, string $branch)
    {
        $params = [
            'repository' => $this->encodeRepository($repository),
            'branch'     => $branch,
        ];
        $url = Str::moustaches(
            $this->headUrl,
            $params
        );
        $data = $this->getJSON($url);

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
        // $closure(
        //     'info',
        //     'Initiating codebase download using Gitlab provider'
        // );

        // Download the code tarball
        $filename = tempnam('/tmp', 'deploy-' . $params['sha'] . '-');
        if (!$handle = fopen($filename, "w")) {
            $closure('error', "Unable to open temporary download file: {$filename}");
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
                implode("\n", [
                    'Error downloading codebase',
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
                implode("\n", [
                    'Error downloading codebase',
                    "Filename - {$filename}",
                    "Status code - {$statusCode}",
                ])
            );
            throw new RuntimeException('Failed to download codebase - ' . $statusCode);
        }

        // Make sure the deployment download directory exists
        if (!is_dir($directory)) {
            $mode = Settings::get('build.chmod.default_folder', Builder::MODE_DEFAULT);
            $closure('info', "Creating deployment directory: {$directory}");

            if (!mkdir($directory, $mode, true)) {
                $closure('error', "Failed to create deployment directory: {$directory}");
                throw new RuntimeException(
                    'Unable to create build directory at ' . $directory
                );
            }
        }

        // Decompress the archive into the download directory
        $tar     = Settings::get('binary.tar', '/bin/tar');
        $command = "{$tar} --strip-components=1 -xzf {$filename} -C {$directory}";
        $closure('info', "Unpacking codebase tarball: {$command}");
        $process = new Process(explode(' ', $command));
        $process->run();
        if (!$process->isSuccessful()) {
            $closure(
                'error',
                implode("\n", [
                    "Unpack failed: {$command}",
                    $process->getErrorOutput(),
                ])
            );
            throw new ProcessFailedException($process);
        }

        // Remove the downloaded archive
        if (!unlink($filename)) {
            $closure(
                'error',
                implode("\n", [
                    'Unable to remove tarball after unpacking',
                    $process->getOutput(),
                ])
            );
            throw new RuntimeException('Unable to remove local code archive');
        }

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
        $closure('info', "Querying Gitlab API: {$url}");
        $curl = $this->getCurlHandle($url);
        $json = curl_exec($curl);
        if (false === $json || !is_string($json)) {
            $closure(
                'error',
                implode("\n", [
                    'Gitlab API request failed',
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
                implode("\n", [
                    'Failed to parse Gitlab response json',
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
                implode("\n", [
                    'YAML deployment configuration read successfully',
                    "JSON - " . $json
                ])
            );
        } catch (Exception $ex) {
            $closure(
                'error',
                implode("\n", [
                    'Unable to parse YAML deployment configuration',
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
        $curl = parent::getCurlHandle($url);
        curl_setopt_array($curl, [
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
