<?php

namespace App\Console\Command\Queue;

use Ronanchilvers\Foundation\Facade\Queue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to watch a given queue for jobs
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class WatchCommand extends Command
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function configure()
    {
        $this
            ->setName('queue:watch')
            ->setDescription('Watch a given queue for jobs')
            ->addArgument(
                'queue',
                InputArgument::REQUIRED,
                'The queue to watch for jobs'
            )
            ->addOption(
                'timeout',
                't',
                InputOption::VALUE_REQUIRED,
                'The timeout for reserving jobs',
                5
            )
            ->addOption(
                'iterations',
                'i',
                InputOption::VALUE_REQUIRED,
                'The maximum number of iterations',
                null
            )
            ;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = $input->getArgument('queue');
        $timeout = $input->getOption('timeout');
        $iterations = $input->getOption('iterations');

        $output->writeln('Starting watch...');
        $output->writeln('Queue : ' . $queue);
        $output->writeln('Timeout : ' . $timeout);
        $output->writeln('Iterations : ' . ($iterations ?: 'unlimited'));
        Queue::watch(
            $queue,
            $timeout,
            $iterations,
            $output
        );
    }
}
