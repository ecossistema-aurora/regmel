<?php

declare(strict_types=1);

namespace App\Regmel\Command;

use App\Entity\Agent;
use App\Entity\User;
use App\Enum\UserStatusEnum;
use App\Security\PasswordHasher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;

class CreateDemoForRegmelCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('------------------------------------');

        $user = new User();
        $user->setFirstName('Admin');
        $user->setLastName('Regmel');
        $user->setEmail('admin@regmel.com');
        $user->setPassword(PasswordHasher::hash('Aurora@2024'));
        $user->setStatus(UserStatusEnum::ACTIVE->value);
        $user->setId(Uuid::fromString('7dafebe2-a47c-4119-a81d-3257fc721026'));

        $agent = new Agent();
        $agent->setId(Uuid::fromString('7dafebe2-a47c-4119-a81d-3257fc721026'));
        $agent->createFromUser($user);

        $user->addAgent($agent);

        $this->entityManager->persist($user);
        $this->entityManager->persist($agent);
        $this->entityManager->flush();

        $output->writeln('Created Admin User');
        $output->writeln('User: admin@regmel.com');
        $output->writeln('Pass: Aurora@2024');
        $output->writeln('------------------------------------');

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:demo-regmel')
            ->setDescription('Run command that create a new admin user.');
    }
}
