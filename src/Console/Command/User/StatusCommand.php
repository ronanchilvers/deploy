<?php

namespace App\Console\Command\User;

use App\Model\User;
use Ronanchilvers\Orm\Orm;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to create users
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class StatusCommand extends Command
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function configure()
    {
        $this
            ->setName('user:status')
            ->setDescription('Update the status for an existing user')
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'The email address of the user'
            )
            ->addArgument(
                'action',
                InputArgument::REQUIRED,
                'The action to take - one of \'activate\' or \'deactivate\''
            )
            ;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $email = trim($email);

        $action = $input->getArgument('action');
        $action = trim($action);

        if (!in_array($action, ['activate', 'deactivate'])) {
            throw new RuntimeException('Invalid action ' . $action);
        }

        $output->writeln('Email : ' . $email);
        $output->writeln('Action : ' . $action);

        $user = Orm::finder(User::class)->select()
            ->where(User::prefix('email'), $email)
            ->one();
        if (!$user instanceof User) {
            throw new RuntimeException('User not found for email ' . $email);
        }
        $user->status = ($action == 'activate') ? User::STATUS_ACTIVE : User::STATUS_INACTIVE;
        if (!$user->saveWithValidation()) {
            $errors = [];
            foreach ($user->getErrors() as $fieldErrors) {
                $errors = array_merge($errors, $fieldErrors);
            }
            $errors = implode(',', $errors);
            throw new RuntimeException('Unable to save user - ' . $errors);
        }
        $output->writeln("User {$email} updated");

        return 0;
    }
}
