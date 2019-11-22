<?php

namespace App\Provider;

use App\Builder;
use App\Facades\Log;
use App\Facades\Settings;
use App\Model\Deployment;
use App\Model\Project;
use Closure;
use Exception;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use ReflectionClass;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Utility\Str;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

/**
 * Base provider class
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
abstract class AbstractProvider
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var array
     */
    protected $typesHandled = [];

    /**
     * @var string
     */
    protected $headUrl = null;

    /**
     * @var string
     */
    protected $branchAndTagUrl = null;

    /**
     * @var string
     */
    protected $commitUrl = null;

    /**
     * @var string
     */
    protected $downloadUrl = null;

    /**
     * @var string
     */
    protected $configUrl = null;

    /**
     * @var string
     */
    protected $repoUrl = null;

    /**
     * @var string
     */
    protected $branchUrl = null;

    /**
     * @var string
     */
    protected $shaUrl = null;

    /**
     * Class constructor
     *
     * @param string $token
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(ClientInterface $client, string $token)
    {
        $this->client = $client;
        $this->token = $token;
    }

    /**
     * @see \App\Provider\ProviderInterface::handles()
     */
    public function handles(Project $project)
    {
        return in_array(
            $project->provider,
            $this->typesHandled
        );
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function getLabel()
    {
        $reflection = new ReflectionClass($this);

        return strtolower($reflection->getShortName());
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

    public function getTagsAndBranches(string $repository)
    {
    }

    /**
     * @see \App\Provider\ProviderInterface::scanConfiguration()
     */
    public function scanConfiguration(Project $project, Deployment $deployment, Closure $closure = null)
    {
        $repository = $this->encodeRepository($project->repository);
        $params = [
            'repository' => $repository,
            'sha'        => $deployment->sha,
        ];
        $url = Str::moustaches(
            $this->configUrl,
            $params
        );
        $data = $this->getJSON($url);
        $yaml = base64_decode($data['content']);
        try {
            $yaml = Yaml::parse($yaml);
            $closure(
                'info',
                implode("\n", [
                    'YAML deployment configuration read successfully',
                    "JSON: " . json_encode($data, JSON_PRETTY_PRINT)
                ])
            );
        } catch (Exception $ex) {
            $closure(
                'error',
                implode("\n", [
                    'Unable to parse YAML deployment configuration',
                    "Exception: " . $ex->getMessage(),
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
     * @see \App\Provider\ProviderInterface::download()
     */
    public function download(Project $project, Deployment $deployment, $directory, Closure $closure = null)
    {
        $repository = $this->encodeRepository($project->repository);
        $params = [
            'repository' => $repository,
            'sha'        => $deployment->sha,
        ];
        $url = Str::moustaches(
            $this->downloadUrl,
            $params
        );
        $filename = tempnam('/tmp', Str::join('-', 'deploy', $project->id, $params['sha']));
        if (!$handle = fopen($filename, "w")) {
            $closure(
                'error',
                implode("\n", [
                    "Unable to open temporary download file: {$filename}"
                ])
            );
            throw new RuntimeException('Unable to open temporary file');
        }
        $response = $this->get(
            $url,
            [
                'sink' => $handle,
            ]
        );

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
     * Send a GET request to a URL and get back a JSON array
     *
     * @return array
     * @throws RuntimeException
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getJSON($url, array $options = []): array
    {
        $response = $this->get($url, $options);
        $body = $response->getBody();
        if (!$body instanceof StreamInterface) {
            throw new RuntimeException($this->getLabel() . ' : Unable to read response body');
        }
        if (!$data = json_decode($body->getContents(), true)) {
            throw new RuntimeException($this->getLabel() . ' : Invalid JSON response for HEAD information request');
        }

        return $data;
    }

    /**
     * Send a GET request to a URL
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function get($url, array $options = []): ResponseInterface
    {
        return $this->client()->request(
            'GET',
            $url,
            $options
        );
    }

    /**
     * Get the HTTP client object
     *
     * @return \GuzzleHttp\ClientInterface
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function client(): ClientInterface
    {
        return $this->client;
    }

    /**
     * Encode a repository name
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function encodeRepository($repository)
    {
        return $repository;
    }
}
