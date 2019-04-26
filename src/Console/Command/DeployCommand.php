<?php

namespace App\Console\Command;

use App\Builder;
use App\Builder\BuildException;
use App\Facades\Settings;
use App\Model\Project;
use Exception;
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
        $id = $input->getArgument('id');
        $project = Project::find($id);
        if (!$project instanceof Project) {
            throw new RuntimeException('Invalid project id');
        }

        try {
            $baseDir = Settings::get('build.base_dir');
            if (!$baseDir) {
                throw new Exception('Base build directory not configured');
            }
            $builder = new Builder($baseDir, $project);

            // Scan the project for a yaml file
            $builder->scan();

            // Initialise the project
            $builder->initialise();

            // Prepare the new release
            $builder->prepare();

            // Finalise the new release
            $builder->finalise();

        } catch (BuildException $ex) {
            $output->writeln($ex->getMessage());
            throw $ex;
        }
    }
}
