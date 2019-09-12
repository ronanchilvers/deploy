<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use App\Facades\Log;
use App\Model\Project;
use App\Model\Release;
use App\Provider\Factory;
use App\Provider\ProviderInterface;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Utility\File;
use RuntimeException;

/**
 * Action to checkout the project from source control
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class CheckoutAction extends AbstractAction implements ActionInterface
{
    /**
     * @var App\Provider\ProviderInterface
     */
    protected $provider;

    /**
     * Class constructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @see App\Action\ActionInterface::run()
     */
    public function run(Config $configuration, Context $context)
    {
        $releaseBaseDir = $context->get('release_base_dir');
        if (is_null($releaseBaseDir)) {
            throw new RuntimeException('Invalid or missing release_dir');
        }
        $project = $context->get('project');
        if (!$project instanceof Project) {
            throw new RuntimeException('Invalid or missing project');
        }
        $release = $context->get('release');
        if (!$release instanceof Release) {
            throw new RuntimeException('Invalid or missing release');
        }
        $releaseDir = File::join(
            $releaseBaseDir,
            $release->number
        );
        $context->set(
            'release_dir',
            $releaseDir
        );
        $head = $this->provider->getHeadInfo(
            $project
        );
        Log::debug('Updating release commit information', $head);
        $release->sha     = $head['sha'];
        $release->author  = $head['author'];
        $release->message = $head['message'];
        if (!$release->save()) {
            throw new RuntimeException('Unable to update release with commit information');
        }
        $params = [
            'repository' => $project->repository,
            'sha'        => $release->sha,
        ];
        Log::debug('Downloading codebase', $params);
        $this->provider->download(
            $params,
            $releaseDir
        );
    }
}
