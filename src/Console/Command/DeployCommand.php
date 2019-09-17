<?php

namespace App\Console\Command;

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
use App\Builder\BuildException;
use App\Facades\Provider;
use App\Facades\Settings;
use App\Model\Project;
use App\Model\Release;
use Exception;
use Ronanchilvers\Foundation\Config;
use Ronanchilvers\Orm\Orm;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Perform a deployment for a site
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class DeployCommand extends Command
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function configure()
    {
        $this
            ->setName('project:deploy')
            ->setDescription('Deploy a project')
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                'The id of the project to deploy'
            )
        ;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $id            = $input->getArgument('id');
            $project       = Orm::finder(Project::class)->one($id);
            if (!$project instanceof Project) {
                throw new RuntimeException('Invalid project id');
            }
            $release       = Orm::finder(Release::class)->nextForProject($project);
            $release->save();
            $data          = Yaml::parseFile(__DIR__ . '/../../../config/defaults.yaml');
            $configuration = new Config($data);
            $builder       = new Builder(
                $project,
                $release,
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

            if (!$release->start()) {
                throw new RuntimeException('Unable to mark the release as started');
            }
            $builder->run($configuration, function ($data) use ($output) {
                $output->writeln($data);
            });
            if (!$release->finish()) {
                throw new RuntimeException('Unable to mark the release as finished');
            }
        } catch (Exception $ex) {
            $output->writeln($ex->getMessage());
            if (!$release->fail()) {
                throw new RuntimeException('Unable to mark the release as failed');
            }
            throw $ex;
        }
    }
}
