<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;

#[AsCommand(
    name: 'app:load-initial-users',
    description: 'Create default users for the demo application',
)]
class LoadInitialUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        // no arguments
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = [
            ['user1@gmail.com', '1234', ['ROLE_USER'], 'User One'],
            ['user2@gmail.com', '1234', ['ROLE_USER'], 'User Two'],
            ['uadmin@gmail.com', '1234', ['ROLE_ADMIN'], 'Admin'],
        ];

        foreach ($users as [$email, $plainPassword, $roles, $name]) {
            if ($this->em->getRepository(User::class)->findOneBy(['email' => $email])) {
                continue;
            }

            $user = new User();
            $user->setEmail($email)
                ->setRoles($roles)
                ->setName($name)
                ->setPassword($this->hasher->hashPassword($user, $plainPassword));

            $this->em->persist($user);
        }

        $this->em->flush();

        $io->success('Default users created or already exist.');

        return Command::SUCCESS;
    }
}
