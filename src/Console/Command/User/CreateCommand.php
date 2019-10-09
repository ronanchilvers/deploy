<?php

namespace App\Console\Command\User;

use App\Model\User;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Command to create users
 *
 * @author Ronan Chilvers <ronan@d3r.com>
 */
class CreateCommand extends Command
{
    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    public function configure()
    {
        $this
            ->setName('user:create')
            ->setDescription('Create a user')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name for the user'
            )
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'The email address for the user'
            )
            ;
    }

    /**
     * @author Ronan Chilvers <ronan@d3r.com>
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $email = $input->getArgument('email');

        $output->writeln('Creating new user...');
        $output->writeln('Name : ' . $name);
        $output->writeln('Email : ' . $email);
        $helper = $this->getHelper('question');

        $question = new Question('Enter the password for the new user : ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $confirm = new Question('Confirm the password : ');
        $confirm->setHidden(true);
        $confirm->setHiddenFallback(false);

        $password     = $helper->ask($input, $output, $question);
        $confirmation = $helper->ask($input, $output, $confirm);
        $password     = trim($password);
        $confirmation = trim($confirmation);
        if ($password !== $confirmation) {
            throw new RuntimeException('Password does not match confirmation');
        }

        $output->writeln('Creating user record...');
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = password_hash($password, PASSWORD_DEFAULT);
        if (!$user->saveWithValidation()) {
            $errors = [];
            foreach ($user->getErrors() as $fieldErrors) {
                $errors = array_merge($errors, $fieldErrors);
            }
            $errors = implode(',', $errors);
            throw new RuntimeException('Unable to create user - ' . $errors);
        }
        $output->writeln("User {$email} created");
    }
}
