<?php

namespace App\Queue;

use App\Action\ActivateAction;
use App\Action\CheckoutAction;
use App\Action\CleanupAction;
use App\Action\ClearPathsAction;
use App\Action\ComposerAction;
use App\Action\CreateWorkspaceAction;
use App\Action\FinaliseAction;
use App\Action\ScanConfigurationAction;
use App\Action\SharedAction;
use App\Action\WritablesAction;
use App\Builder;
use App\Facades\Log;
use App\Facades\Provider;
use App\Model\Deployment;
use App\Model\Project;
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
     * @var App\Model\deployment
     */
    protected $deployment;

    /**
     * Class constructor
     *
     * @param App\Model\Deployment $project
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function __construct(Deployment $deployment)
    {
        $this->deployment = $deployment;
    }

    /**
     * {@inheritdoc}
     *
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function execute()
    {
        try {
            $project       = $this->deployment->project;
            $data          = Yaml::parseFile(__DIR__ . '/../../config/defaults.yaml');
            $configuration = new Config($data);
            $builder       = new Builder(
                $project,
                $this->deployment,
                $configuration
            );
            $provider = Provider::forProject($project);
            $builder->addAction(new ScanConfigurationAction($provider));
            $builder->addAction(new CreateWorkspaceAction);
            $builder->addAction(new CheckoutAction($provider));
            $builder->addAction(new ComposerAction);
            $builder->addAction(new SharedAction);
            $builder->addAction(new WritablesAction);
            $builder->addAction(new ClearPathsAction);
            $builder->addAction(new ActivateAction);
            $builder->addAction(new FinaliseAction);
            $builder->addAction(new CleanupAction);

            if (!$this->deployment->start()) {
                throw new RuntimeException('Unable to mark the deployment as started');
            }
            $builder->run($configuration, function ($data) use ($project) {
                Log::debug($data, [
                    'project' => $project->toArray(),
                ]);
            });
            if (!$this->deployment->finish()) {
                throw new RuntimeException('Unable to mark the deployment as finished');
            }
        } catch (Exception $ex) {
            Log::critical($ex->getMessage(), [
                'project'   => $project->toArray(),
                'deployment'   => $this->deployment->toArray(),
                'exception' => $ex,
            ]);
            if (!$this->deployment->fail()) {
                throw new RuntimeException('Unable to mark the deployment as failed');
            }
            $project->last_status = $this->deployment->status;
            if (!$project->save()) {
                throw new RuntimeException('Unable to project as failed');
            }
            throw $ex;
        }
    }
}
