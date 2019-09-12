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
use App\Facades\Provider;
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

/**
 * Perform a deployment for a site
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class TestCommand extends Command
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function configure()
    {
        $this
            ->setName('test')
            ->setDescription('Test command')
        ;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->getApplication()->getContainer()->get('configuration');
        $project       = Orm::finder(Project::class)->one(1);
        $release       = Orm::finder(Release::class)->nextForProject($project);

        // Save the release
        $release->save();

        $builder = new Builder(
            $project,
            $release,
            $configuration
        );
        $builder->addAction(new ScanConfigurationAction(Provider::getService()));
        $builder->addAction(new CreateWorkspaceAction);
        $builder->addAction(new CheckoutAction(Provider::getService()));
        $builder->addAction(new ComposerAction);
        $builder->addAction(new SharedAction);
        $builder->addAction(new WritablesAction);
        $builder->addAction(new ActivateAction);
        $builder->addAction(new FinaliseAction);
        $builder->addAction(new CleanupAction);

        $builder->run($configuration, function ($data) use ($output) {
            $output->writeln($data);
        });

        // @TODO Remove var_dump
        // var_dump($builder); exit();
    }
}
