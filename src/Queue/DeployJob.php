<?php

namespace App\Queue;

use App\Action\ActivateAction;
use App\Action\CheckoutAction;
use App\Action\CleanupAction;
use App\Action\ComposerAction;
use App\Action\CreateWorkspaceAction;
use App\Action\FinaliseAction;
use App\Action\ScanConfigurationAction;
use App\Action\SharedAction;
use App\Action\WritablesAction;
use App\Builder;
use App\Facades\Log;
use App\Facades\Provider;
use App\Model\Project;
use App\Model\Release;
use Exception;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Foundation\Queue\Job\Job;
use Ronanchilvers\Orm\Orm;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * Deploy a project
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class DeployJob extends Job
{
    /**
     * @var string
     */
    protected $queue = 'deploy';

    /**
     * @var App\Model\Release
     */
    protected $release;

    /**
     * Class constructor
     *
     * @param App\Model\Release $project
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(Release $release)
    {
        $this->release = $release;
    }

    /**
     * {@inheritdoc}
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function execute()
    {
        try {
            $project       = $this->release->project;
            $data          = Yaml::parseFile(__DIR__ . '/../../config/defaults.yaml');
            $configuration = new Config($data);
            $builder       = new Builder(
                $project,
                $this->release,
                $configuration
            );
            $provider = Provider::forProject($project);
            $builder->addAction(new ScanConfigurationAction($provider));
            $builder->addAction(new CreateWorkspaceAction);
            $builder->addAction(new CheckoutAction($provider));
            $builder->addAction(new ComposerAction);
            $builder->addAction(new SharedAction);
            $builder->addAction(new WritablesAction);
            $builder->addAction(new ActivateAction);
            $builder->addAction(new FinaliseAction);
            $builder->addAction(new CleanupAction);

            if (!$this->release->start()) {
                throw new RuntimeException('Unable to mark the release as started');
            }
            $builder->run($configuration, function ($data) use ($project) {
                Log::debug($data, [
                    'project' => $project->toArray(),
                ]);
            });
            if (!$this->release->finish()) {
                throw new RuntimeException('Unable to mark the release as finished');
            }
        } catch (Exception $ex) {
            Log::critical($ex->getMessage(), [
                'project'   => $project->toArray(),
                'release'   => $release->toArray(),
                'exception' => $ex,
            ]);
            if (!$this->release->fail()) {
                throw new RuntimeException('Unable to mark the release as failed');
            }
            throw $ex;
        }
    }
}
