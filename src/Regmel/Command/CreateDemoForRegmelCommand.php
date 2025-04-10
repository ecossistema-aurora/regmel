<?php

declare(strict_types=1);

namespace App\Regmel\Command;

use App\Entity\Opportunity;
use App\Entity\User;
use App\Enum\OrganizationTypeEnum;
use App\Repository\Interface\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Uid\Uuid;

class CreateDemoForRegmelCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepositoryInterface $userRepository,
        protected TokenStorageInterface $tokenStorage,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $output->writeln('------------------------------------');

        $user = $this->userRepository->findOneBy(['email' => 'admin@regmel.com']);
        $agent = $user->getAgents()->first();

        $opportunityForMunicipality = new Opportunity();
        $opportunityForMunicipality->setId(Uuid::fromString('d67f0342-0da0-4b89-b841-caea111e4866'));
        $opportunityForMunicipality->setName('Adesão dos Municípios - 2025');
        $opportunityForMunicipality->setCreatedBy($agent);
        $opportunityForMunicipality->setImage('/img/regmel/icon.svg');
        $opportunityForMunicipality->setExtraFields([
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
        ]);

        $opportunityForCompany = new Opportunity();
        $opportunityForCompany->setId(Uuid::fromString('5a3df77e-62de-43ee-8492-fb2283aa2fe8'));
        $opportunityForCompany->setName('Propostas das Empresas/OSCs - 2025');
        $opportunityForCompany->setCreatedBy($agent);
        $opportunityForCompany->setImage('/img/regmel/icon.svg');
        $opportunityForMunicipality->setExtraFields([
            'type' => OrganizationTypeEnum::EMPRESA->value,
        ]);

        $this->manualLogin($user);

        $this->entityManager->persist($opportunityForMunicipality);
        $this->entityManager->persist($opportunityForCompany);
        $this->entityManager->flush();

        $io->title('Created Opportunity for Municipality: ');
        $output->writeln('Name: '.$opportunityForMunicipality->getName());

        $output->writeln('------------------------------------'.PHP_EOL);
        $io->title('Created Opportunity for Company: ');
        $output->writeln('Name: '.$opportunityForCompany->getName());
        $output->writeln('------------------------------------');

        return Command::SUCCESS;
    }

    private function manualLogin(User $user): void
    {
        $token = new UsernamePasswordToken($user, 'web');
        $this->tokenStorage->setToken($token);
    }

    protected function configure(): void
    {
        $this
            ->setName('app:demo-regmel')
            ->setDescription('Run command that prepare data for regmel 2025.');
    }
}
