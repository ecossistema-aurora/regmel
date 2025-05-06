<?php

declare(strict_types=1);

namespace App\Regmel\Service;

use App\Regmel\Service\Interface\MunicipalityDocumentServiceInterface;
use App\Repository\Interface\OrganizationRepositoryInterface;
use App\Service\Interface\OrganizationServiceInterface;
use Symfony\Component\Uid\Uuid;

readonly class MunicipalityDocumentService implements MunicipalityDocumentServiceInterface
{
    public function __construct(
        private OrganizationServiceInterface $organizationService,
        private OrganizationRepositoryInterface $organizationRepository,
    ) {
    }

    public function decision(Uuid $municipalityId, bool $approved, string $reason): void
    {
        $municipality = $this->organizationService->get($municipalityId);
        $extraFields = $municipality->getExtraFields();
        $extraFields['term_status'] = $approved ? 'approved' : 'rejected';
        $extraFields['term_reason'] = $reason;

        $municipality->setExtraFields($extraFields);
        $this->organizationRepository->save($municipality);
    }
}
