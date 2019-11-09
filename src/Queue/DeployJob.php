<?php

namespace App\Queue;

use App\Action\ActivateAction;
use App\Action\CheckoutAction;
use App\Action\CleanupAction;
use App\Action\ClearPathsAction;
use App\Action\ComposerAction;
use App\Action\Context;
use App\Action\CreateWorkspaceAction;
use App\Action\FinaliseAction;
use App\Action\ScanConfigurationAction;
use App\Action\SharedAction;
use App\Action\WritablesAction;
use App\Builder;
use App\Facades\Log;
use App\Facades\Notifier;
use App\Facades\Provider;
use App\Facades\Settings;
use App\Model\Deployment;
use App\Model\Project;
use Exception;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Foundation\Queue\Exception\FatalException;
use Ronanchilvers\Foundation\Queue\Job\Job;
use Ronanchilvers\Orm\Orm;
use Ronanchilvers\Utility\File;
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
     * @var \App\Model\deployment
     */
    protected $deployment;

    /**
     * Class constructor
     *
     * @param \App\Model\Deployment $project
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
        $project       = $this->deployment->project;
        $data          = Yaml::parseFile(__DIR__ . '/../../config/defaults.yaml');
        $configuration = new Config($data);
        $builder       = new Builder(
            $project,
            $this->deployment
        );
        $baseDir    = Settings::get('build.base_dir');
        $key        = $project->key;
        $projectDir = File::join(
            $baseDir,
            $key
        );
        $deploymentBaseDir = File::join(
            $projectDir,
            'deployments'
        );
        $deploymentDir = File::join(
            $deploymentBaseDir,
            $this->deployment->number
        );
        try {
            $context = new Context;
            $context->set('project_base_dir', $projectDir);
            $context->set('deployment_base_dir', $deploymentBaseDir);
            $context->set('deployment_dir', $deploymentDir);
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
            $builder->run(
                $configuration,
                $context,
                function($data) use ($project) {
                    Log::debug($data, [
                        'project' => $project->toArray(),
                    ]);
                }
            );
            if (!$this->deployment->finish()) {
                throw new RuntimeException('Unable to mark the deployment as finished');
            }

            Notifier::send(
                sprintf(
                    "Deployment completed for <%s|%s>\nSHA: <%s|%s>\nAuthor: %s",
                    $provider->getRepositoryLink($project->repository),
                    $project->repository,
                    $provider->getShaLink($project->repository, $this->deployment->sha),
                    $this->deployment->sha,
                    $this->deployment->author
                ),
                $configuration->get('notify', [])
            );
        } catch (Exception $ex) {
            Log::critical($ex->getMessage(), [
                'project'   => $project->toArray(),
                'deployment'   => $this->deployment->toArray(),
                'exception' => $ex,
            ]);
            if (!$this->deployment->fail()) {
                throw new RuntimeException('Unable to mark the deployment as failed');
            }
            Notifier::send(
                sprintf(
                    "Deployment failed for <%s|%s>\nSHA: <%s|%s>\nAuthor: %s",
                    $provider->getRepositoryLink($project->repository),
                    $project->repository,
                    $provider->getShaLink($project->repository, $this->deployment->sha),
                    $this->deployment->sha,
                    $this->deployment->author
                ),
                $configuration->get('notify', [])
            );
            throw new FatalException(
                $ex->getMessage(),
                $ex->getCode(),
                $ex
            );
        } finally {
            $project->updateFromDeployment($this->deployment);
            if (!$project->markActive()) {
                throw new RuntimeException('Unable to update project details');
            }
        }
    }
}
