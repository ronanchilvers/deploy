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
     * Encode a repository name
     *
     * @return string
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function encodeRepository($repository)
    {
        return str_replace(
            '.',
            '%2E',
            urlencode(
                $repository
            )
        );
    }
}
