<?php

namespace App\Action;

use App\Action\AbstractAction;
use App\Action\ActionInterface;
use App\Model\Project;
use App\Model\Release;
use App\Provider\Factory;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Utility\Str;
use RuntimeException;

/**
 * Action to configure the current release with the correct commit information
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class ConfigureReleaseAction extends AbstractAction implements ActionInterface
{
    /**
     * @var App\Provider\Factory
     */
    protected $factory;

    /**
     * Class constructor
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @see App\Action\ActionInterface::run()
     */
    public function run(Config $configuration, Context $context)
    {
        $release = $context->get('release');
        if (!$release instanceof Release) {
            throw new RuntimeException('Invalid or missing release');
        }
        $project = $context->get('project');
        if (!$project instanceof Project) {
            throw new RuntimeException('Invalid or missing project');
        }

        $provider = $this->factory->forProject($project);
        $provider->getHeadInfo(
            $project
        );
    }
}
