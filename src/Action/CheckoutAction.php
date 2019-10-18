<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use App\Facades\Log;
use App\Provider\ProviderInterface;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Utility\File;

/**
 * Action to checkout the project from source control
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class CheckoutAction extends AbstractAction
{
    /**
     * @var \App\Provider\ProviderInterface
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
     * @see \App\Action\ActionInterface::run()
     */
    public function run(Config $configuration, Context $context)
    {
        $deploymentBaseDir = $context->getOrThrow('deployment_base_dir', 'Invalid or missing deployment_dir');
        $project           = $context->getOrThrow('project', 'Invalid or missing project');
        $deployment        = $context->getOrThrow('deployment', 'Invalid or missing deployment');
        $deploymentDir     = File::join(
            $deploymentBaseDir,
            $deployment->number
        );
        $context->set(
            'deployment_dir',
            $deploymentDir
        );
        $this->info(
            $deployment,
            'Downloading codebase',
            [
                "Repository - {$project->repository}",
                "SHA - {$deployment->sha}",
            ]
        );
        Log::debug('Downloading codebase', [
            'project'    => $project->toArray(),
            'deployment' => $deployment->toArray(),
        ]);
        $this->provider->download(
            $project,
            $deployment,
            $deploymentDir,
            function ($type, $header, $detail = '') use ($deployment) {
                $this->eventFinder->event(
                    $type,
                    $deployment,
                    $header,
                    $detail
                );
            }
        );
    }
}
