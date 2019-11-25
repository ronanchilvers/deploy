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
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\StreamInterface;
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
class Github extends AbstractProvider implements ProviderInterface
{
    /**
     * @var array
     */
    protected $typesHandled = ['github'];

    /**
     * @var string
     */
    protected $headUrl = 'https://api.github.com/repos/{repository}/git/refs/{ref}';

    /**
     * @var string
     */
    protected $branchesUrl = 'https://api.github.com/repos/{repository}/branches';

    /**
     * @var string
     */
    protected $tagsUrl = 'https://api.github.com/repos/{repository}/tags';

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
     * @see \App\Provider\ProviderInterface::getHeadInfo()
     */
    public function getHeadInfo(string $repository, string $type, string $ref)
    {
        $type = ('tag' == $type) ? 'tags' : 'heads';
        $params = [
            'repository' => $repository,
            'ref'     => $type . '/' . $ref,
        ];
        $url = Str::moustaches(
            $this->headUrl,
            $params
        );
        $data = $this->getJSON($url);
        $params['sha'] = $data['object']['sha'];
        $url = Str::moustaches(
            $this->commitUrl,
            $params
        );
        $data = $this->getJSON($url);

        return [
            'sha'       => $data['sha'],
            'author'    => $data['commit']['author']['email'],
            'committer' => $data['commit']['committer']['email'],
            'message'   => $data['commit']['message'],
        ];
    }
}
