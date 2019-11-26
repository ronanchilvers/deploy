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
    protected $token;

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
    protected $downloadUrl = 'https://api.bitbucket.org/2.0/{repository}/repository/archive.tar.gz?sha={sha}';

    /**
     * @var string
     */
    protected $configUrl = 'https://api.bitbucket.org/2.0/{repository}/repository/files/deploy.yaml?ref={sha}';

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
    public function getHeadInfo(string $repository, string $type, string $ref)
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

        return [
            'sha'       => $data['id'],
            'author'    => $data['author_email'],
            'committer' => $data['committer_email'],
            'message'   => $data['title'],
        ];
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
