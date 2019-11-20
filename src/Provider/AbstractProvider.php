<?php

namespace App\Provider;

use App\Model\Project;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use ReflectionClass;
use Ronanchilvers\Utility\Str;
use RuntimeException;

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
        ]);

        return $curl;
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
}
