<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use App\Action\Context;
use App\Facades\Log;
use App\Model\Project;
use App\Model\Release;
use App\Provider\ProviderInterface;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Utility\Str;
use RuntimeException;

/**
 * Action to scan the project repository for configuration information
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ScanConfigurationAction extends AbstractAction implements ActionInterface
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
        $project             = $context->getOrThrow('project', 'Invalid or missing project');
        $remoteConfiguration = $this->provider->scanConfiguration(
            $project
        );
        if (is_null($remoteConfiguration)) {
            return;
        }
        Log::debug('Merging remote configuration', [
            'current' => $configuration->getAll(),
            'remote'  => $remoteConfiguration->getAll(),
        ]);
        $configuration->merge($remoteConfiguration);
        Log::debug('Merged configuration', [
            'current' => $configuration->getAll(),
        ]);
    }
}
