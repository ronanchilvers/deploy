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
 * Bitbucket source control provider
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class Bitbucket extends AbstractProvider implements ProviderInterface
{
    /**
     * @var array
     */
    protected $typesHandled = ['bitbucket'];

    /**
     * @var string
     */
    protected $headUrl = 'https://api.bitbucket.org/2.0/repositories/{repository}/commits/{branch}?pagelen=1';

    /**
     * @var string
     */
    protected $branchesUrl = 'https://api.bitbucket.org/2.0/repositories/{repository}/refs/branches?pagelen=50';

    /**
     * @var string
     */
    protected $tagsUrl = 'https://api.bitbucket.org/2.0/repositories/{repository}/refs/tags?pagelen=50';

    /**
     * @var string
     */
    protected $downloadUrl = 'https://bitbucket.org/{repository}/get/{sha}.zip';

    /**
     * @var string
     */
    protected $configUrl = 'https://api.bitbucket.org/2.0/repositories/{repository}/src/{sha}/deploy.yaml';

    /**
     * @var string
     */
    protected $repoUrl = 'https://bitbucket.org/{repository}';

    /**
     * @var string
     */
    protected $branchUrl = 'https://bitbucket.org/{repository}/src/{branch}';

    /**
     * @var string
     */
    protected $shaUrl = 'https://bitbucket.org/{repository}/commits/{sha}';

    /**
     * @see \App\Provider\ProviderInterface::getHeadInfo()
     */
    public function getHeadInfo(string $repository, string $ref)
    {
        $params = [
            'repository' => $this->encodeRepository($repository),
            'branch'     => $ref,
        ];
        $url = Str::moustaches(
            $this->headUrl,
            $params
        );
        $data = $this->getJSON($url);
        if (!is_array($data) || !isset($data['values'], $data['values'][0])) {
            throw new RuntimeException('No data found for head commit');
        }

        return [
            'sha'       => $data['values'][0]['hash'],
            'author'    => $data['values'][0]['author']['raw'],
            'committer' => $data['values'][0]['author']['raw'],
            'message'   => $data['values'][0]['summary']['raw'],
        ];
    }

    /**
     * Try to download the deploy.yaml file from the remote repository
     *
     * @param \App\Model\Project $project
     * @param \App\Model\Deployment $deployment
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function getConfiguration(Project $project, Deployment $deployment)
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
        $response = $this->get($url);

        return $response->getBody()->getContents();
    }

    /**
     * Process a ref arrays into simplified form
     *
     * @param array $data
     * @return array
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function processRefArray(array $data): array
    {
        if (!isset($data['values']) || 0 == count($data['values'])) {
            return [];
        }
        $output = [];
        foreach ($data['values'] as $datum) {
            $output[$datum['name']] = $datum['name'];
        }

        return $output;
    }
}
