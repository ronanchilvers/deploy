<?php

namespace App\Provider;

use App\Model\Project;
use ReflectionClass;
use Ronanchilvers\Utility\Str;

/**
 * Base provider class
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
abstract class AbstractProvider
{
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
    public function __construct(string $token)
    {
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
}
