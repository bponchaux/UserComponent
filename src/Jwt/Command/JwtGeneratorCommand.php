<?php

namespace Biig\Component\User\Jwt\Command;

use Biig\Component\User\Jwt\JwtGeneratorInterface;
use Biig\Component\User\Persistence\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class JwtGeneratorCommand extends Command
{
    /**
     * @var JwtGeneratorInterface
     */
    private $generator;

    /**
     * @var RepositoryInterface
     */
    private $userRepository;

    public function __construct(JwtGeneratorInterface $generator, RepositoryInterface $userRepository)
    {
        parent::__construct('biig:jwt:generate');

        $this->generator = $generator;
        $this->userRepository = $userRepository;
    }

    protected function configure()
    {
        $this
            ->setName('biig:jwt:generate')
            ->setDescription('Generate the first user JWT Token.')
            ->addOption('role', 'r', InputOption::VALUE_OPTIONAL, 'Specify what role you want')
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'Specify what email the user should have')
            ->setHelp(<<<HELP
This command is only to use in the context of the tests.

It allows you to get the first user JWT Token which is really useful to debug
tests quickly. In other words, you can simply run this command instead of try to
get the email from database, then encode it with the password in base64, then
doing a request to the login.
HELP
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // In test environment there is not much users and this command do not so much
        // so the following code is acceptable and avoid a modification of the
        // user repository (to add a `getFirstUser` method)
        $users = $this->userRepository->findAll();

        if (empty($users)) {
            $output->writeln('No users provided');

            return;
        }

        $user = reset($users);

        if ($input->hasOption('email')
            && null !== $input->getOption('email'))
        {
            $user = $this->findByEmail($input->getOption('email'), $output, $user, $users);
        }
        else if ($input->hasOption('role')
            && null !== $input->getOption('role')
            && !in_array($input->getOption('role'), $user->getRoles()))
        {
            $user = $this->findOneByRole($input, $output, $users);
        }

        if ($user === null) {
            return ;
        }

        $token = $this->generator->create($user);

        $output->writeln([
            'Username: ' . $user->getUsername(),
            'Roles: ' . implode(', ', $user->getRoles()),
            'Token: ' . $token,
        ]);
    }

    /**
     * @param string          $email
     * @param OutputInterface $output
     * @param                 $user
     * @param array           $users
     *
     * @return mixed
     */
    private function findByEmail(string $email, OutputInterface $output, $user, array $users)
    {
        if (!method_exists($user, 'getEmail')) {
            $output->writeln('<error>User Class does not have a getEmail() method<error>');

            return null;
        }

        foreach ($users as $item) {
            if ($email === $item->getEmail()) {
                return $item;
            }
        }

        $output->writeln(sprintf('<error>User with email "%s" doesn\'t exist.<error>', $input->getOption('email')));

        return null;
    }

    /**
     * @param string          $role
     * @param OutputInterface $output
     * @param array           $users
     *
     * @return mixed
     */
    protected function findOneByRole(string $role, OutputInterface $output, array $users)
    {
        foreach ($users as $item) {
            if (in_array($role, $item->getRoles())) {
                return $item;
            }
        }

        $output->writeln(sprintf('<error>User with role "%s" doesn\'t exist.<error>', $role));

        return null;
    }
}
