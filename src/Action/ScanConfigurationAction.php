<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use App\Action\Context;
use App\Facades\Log;
use App\Provider\ProviderInterface;
use Ronanchilvers\Foundation\Config;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * Action to scan the project repository for configuration information
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ScanConfigurationAction extends AbstractAction
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
        $deployment          = $context->getOrThrow('deployment', 'Invalid or missing deployment');
        $remoteConfiguration = $this->provider->scanConfiguration(
            $project,
            $deployment,
            function ($type, $header, $detail = '') use ($deployment) {
                $this->eventFinder->event(
                    $type,
                    $deployment,
                    $header,
                    $detail
                );
            }
        );
        if (is_null($remoteConfiguration)) {
            $deployment->configuration = Yaml::dump($configuration->getAll(), 10);
            if (!$deployment->save()) {
                throw new RuntimeException('Unable to store configuration data on deployment');
            }
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
        $deployment->configuration = Yaml::dump($configuration->getAll(), 10, 2);
        if (!$deployment->save()) {
            throw new RuntimeException('Unable to store configuration data on deployment');
        }
    }
}
