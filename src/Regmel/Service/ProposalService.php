<?php

declare(strict_types=1);

namespace App\Regmel\Service;

use App\Entity\Initiative;
use App\Entity\InscriptionOpportunity;
use App\Entity\InscriptionPhase;
use App\Entity\Opportunity;
use App\Entity\Organization;
use App\Enum\InscriptionOpportunityStatusEnum;
use App\Enum\InscriptionPhaseStatusEnum;
use App\Enum\OrganizationTypeEnum;
use App\Regmel\Service\Interface\ProposalServiceInterface;
use App\Repository\Interface\InitiativeRepositoryInterface;
use App\Repository\OrganizationRepository;
use App\Service\InitiativeService;
use App\Service\Interface\CityServiceInterface;
use App\Service\Interface\FileServiceInterface;
use App\Service\Interface\OrganizationServiceInterface;
use App\Service\OpportunityService;
use App\Service\PhaseService;
use App\Service\UserService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

class ProposalService implements ProposalServiceInterface
{
    public function __construct(
        private readonly InitiativeService $initiativeService,
        private readonly InitiativeRepositoryInterface $initiativeRepository,
        private readonly UserService $userService,
        private readonly SerializerInterface $serializer,
        private readonly OrganizationServiceInterface $organizationService,
        private readonly OrganizationRepository $organizationRepository,
        private readonly OpportunityService $opportunityService,
        private readonly CityServiceInterface $cityService,
        private readonly FileServiceInterface $fileService,
        private readonly EntityManagerInterface $entityManager,
        private readonly PhaseService $phaseService,
        protected TokenStorageInterface $tokenStorage,
        private readonly Security $security,
    ) {
    }

    public function saveProposal(Organization $company, array $data, ?UploadedFile $map = null, ?UploadedFile $project = null): Initiative
    {
        $user = $this->security->getUser();

        $status = 'Enviada';
        $municipality = $this->organizationRepository->findOrganizationByCityId(
            $data['city']
        );

        $initiative = new Initiative();
        $initiative->setId(Uuid::v4());
        $initiative->setName($data['name']);
        $initiative->setCreatedBy($user->getAgents()->first());
        $initiative->setCreatedAt(new DateTimeImmutable());
        $initiative->setOrganizationFrom($company);
        $initiative->setOrganizationTo($municipality);

        if (null === $municipality) {
            $status = 'Sem Adesão do Municipio';

            $municipality = $this->cityService->get($data['city']);
            $state = $municipality->getState()->getAcronym();
            $cityCode = $municipality->getCityCode();
            $cityName = $municipality->getName().'-'.$state;
            $region = $municipality->getState()->getRegion();
        } else {
            $state = $municipality->getExtraFields()['state'];
            $cityCode = $municipality->getExtraFields()['cityCode'] ?? '';
            $cityName = $municipality->getName().'-'.$state;
            $region = $municipality->getExtraFields()['region'];
        }

        $initiative->setExtraFields([
            'status' => $status,
            'area_size' => (float) $data['area_size'],
            'quantity_houses' => (float) $data['quantity_houses'],
            'area_characteristic' => $data['area_characteristic'],
            'map_file' => $this->uploadFile($map, $cityCode),
            'project_file' => $this->uploadFile($project, $cityCode),
            'city_name' => $cityName,
            'state' => $state,
            'region' => $region,
        ]);

        $this->initiativeRepository->save($initiative);

        return $initiative;
    }

    public function findOpportunitiesBy(OrganizationTypeEnum $enum): array
    {
        $opportunities = $this->opportunityService->findBy();

        return array_filter(
            $opportunities,
            fn (Opportunity $opportunity) => ($opportunity->getExtraFields()['type'] ?? null) === $enum->value
        );
    }

    private function createInscriptionForOrganization(string $opportunityId, Organization $organization): void
    {
        $opportunityId = Uuid::fromString($opportunityId);

        $opportunity = $this->opportunityService->get(
            $opportunityId
        );

        $inscription = new InscriptionOpportunity();
        $inscription->setOpportunity($opportunity);
        $inscription->setOrganization($organization);
        $inscription->setId(Uuid::v4());
        $inscription->setStatus(InscriptionOpportunityStatusEnum::ACTIVE->value);

        $firstPhase = current($this->phaseService->list($opportunityId, 1));

        if (false !== $firstPhase) {
            $inscriptionPhase = new InscriptionPhase();
            $inscriptionPhase->setId(Uuid::v4());
            $inscriptionPhase->setOrganization($inscription->getOrganization());
            $inscriptionPhase->setPhase($firstPhase);
            $inscriptionPhase->setStatus(InscriptionPhaseStatusEnum::ACTIVE->value);

            $this->entityManager->persist($inscriptionPhase);
        }

        $this->entityManager->persist($inscription);
        $this->entityManager->flush();
    }

    private function uploadFile(UploadedFile $uploadedFile, string|int $cityCode): string
    {
        $pdf = $this->fileService->uploadMixedFile(
            $uploadedFile,
            extraPath: '/regmel/company/documents',
            optionalName: 'proposta-01-'.$cityCode,
        );

        return $pdf->getFilename();
    }

    public function isDuplicateOrganization(string $name, string $cityId): bool
    {
        return $this->organizationRepository->isOrganizationDuplicate($name, $cityId);
    }
}
