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
     * @var GeneratorInterface
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
        $user = reset($users);

        if ($input->hasOption('role') && !in_array($input->getOption('role'), $user->getRoles())) {
            foreach ($users as $item) {
                if (in_array($input->getOption('role'), $item->getRoles())) {
                    $user = $item;
                }
            }
        }

        $token = $this->generator->create($user);

        $output->writeln([
            'Username: ' . $user->getUsername(),
            'Roles: ' . implode(', ', $user->getRoles()),
            'Token: ' . $token,
        ]);
    }
}
