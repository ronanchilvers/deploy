<?php

namespace App\Provider;

use App\Builder;
use App\Facades\Log;
use App\Facades\Settings;
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
    protected $headUrl     = 'https://api.gitlab.com/repos/{repository}/git/refs/heads/{branch}';

    /**
     * @var string
     */
    protected $commitUrl   = 'https://api.gitlab.com/repos/{repository}/commits/{sha}';

    /**
     * @var string
     */
    protected $downloadUrl = 'https://api.gitlab.com/repos/{repository}/tarball/{sha}';

    /**
     * @var string
     */
    protected $configUrl   = 'https://api.gitlab.com/repos/{repository}/contents/deploy.yaml';

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
     * @see App\Provider\ProviderInterface::handles()
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
     * @see App\Provider\ProviderInterface::getHeadInfo()
     */
    public function getHeadInfo(Project $project, Closure $closure = null)
    {
        $params = [
            'repository' => $project->repository,
            'branch'     => $project->branch,
        ];
        $url = $this->formatUrl(
            $params,
            $this->headUrl
        );
        $curl = $this->getCurlHandle($url);
        $data = curl_exec($curl);
        curl_close($curl);
        if (!$data = json_decode($data, true)) {
            throw new RuntimeException('Invalid commit data for head');
        }
        $params['sha'] = $data['object']['sha'];
        $url = $this->formatUrl(
            $params,
            $this->commitUrl
        );
        $curl = $this->getCurlHandle($url);
        $data = curl_exec($curl);
        curl_close($curl);
        if (!$data = json_decode($data, true)) {
            throw new RuntimeException('Invalid commit data for ' . $params['sha']);
        }

        return [
            'sha'    => $data['sha'],
            'author' => $data['commit']['author']['email'],
            'message'=> $data['commit']['message'],
        ];
    }

    /**
     * @see App\Provider\ProviderInterface::download()
     */
    public function download($params, $directory, Closure $closure = null)
    {
        $url = $this->formatUrl(
            $params,
            $this->downloadUrl
        );

        // Download the code tarball
        $filename = tempnam('/tmp', 'deploy-' . $params['sha'] . '-');
        if (!$handle = fopen($filename, "w")) {
            throw new RuntimeException('Unable to open temporary file');
        }
        $curl = $this->getCurlHandle($url);
        curl_setopt_array($curl, [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_FILE           => $handle
        ]);
        if (false === curl_exec($curl)) {
            throw new RuntimeException(curl_errno($curl) . ' - ' . curl_error($curl));
        }
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        fclose($handle);
        if($statusCode != 200){
            throw new RuntimeException('Failed to download codebase - ' . $statusCode);
        }

        // Make sure the deployment download directory exists
        if (!is_dir($directory)) {
            $mode = Settings::get('build.chmod.default_folder', Builder::MODE_DEFAULT);
            if (!mkdir($directory, $mode, true)) {
                throw new RuntimeException(
                    'Unable to create build directory at ' . $directory
                );
            }
        }

        // Decompress the archive into the download directory
        $command = explode(' ', "/usr/bin/tar --strip-components=1 -xzf {$filename} -C {$directory}");
        $process = new Process($command);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Remove the downloaded archive
        if (!unlink($filename)) {
            throw new RuntimeException('Unable to remove local code archive');
        }

        return true;
    }

    /**
     * @see App\Provider\ProviderInterface::scanConfiguration()
     */
    public function scanConfiguration(Project $project, Closure $closure = null)
    {
        $url = $this->formatUrl(
            $project->toArray(),
            $this->configUrl
        );
        $curl = $this->getCurlHandle($url);
        $data = curl_exec($curl);
        $info = curl_getinfo($curl);
        if (404 == $info['http_code']) {
            Log::debug('Remote configuration file not found', [
                'project' => $project->toArray(),
            ]);
            return;
        }
        $data = json_decode($data, true);
        if (!$data || !isset($data['content'])) {
            Log::debug('Remote configuration file could not be read', [
                'project' => $project->toArray(),
            ]);
            return;
        }
        $yaml = base64_decode($data['content']);
        $yaml = Yaml::parse($yaml);

        return new Config($yaml);
    }

    /**
     * Format a url for a project
     *
     * @param App\Model\Project $project
     * @param string $urlTemplate
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function formatUrl($params, $urlTemplate)
    {
        $keys = array_map(function ($value) {
            return '{'.$value.'}';
        }, array_keys($params));
        $values = array_values($params);

        return str_replace($keys, $values, $urlTemplate);
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
                "Authorization: token {$this->token}"
            ],
        ]);

        return $curl;
    }
}
