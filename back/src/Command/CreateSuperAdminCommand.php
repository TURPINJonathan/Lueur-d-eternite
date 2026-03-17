<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-super-admin',
    description: 'Crée un utilisateur avec le rôle ROLE_SUPER_ADMIN',
)]
class CreateSuperAdminCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email du super admin')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe en clair')
            ->addArgument('firstname', InputArgument::OPTIONAL, 'Prénom', 'Super')
            ->addArgument('lastname', InputArgument::OPTIONAL, 'Nom', 'Admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = (string) $input->getArgument('email');
        $password = (string) $input->getArgument('password');
        $firstname = (string) $input->getArgument('firstname');
        $lastname = (string) $input->getArgument('lastname');

        if (!filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            $io->error('Email invalide.');

            return Command::INVALID;
        }

        if (mb_strlen($password) < 8) {
            $io->error('Le mot de passe doit contenir au moins 8 caractères.');

            return Command::INVALID;
        }

        if (null !== $this->userRepository->findOneBy(['email' => $email])) {
            $io->error(\sprintf('Un utilisateur existe déjà avec l\'email "%s".', $email));

            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(\sprintf('Super admin créé: %s', $email));

        return Command::SUCCESS;
    }
}
