<?php

declare(strict_types=1);

namespace App\Regmel\Service;

use App\Entity\Organization;
use App\Regmel\Repository\Interface\MunicipalityRepositoryInterface;
use App\Regmel\Service\Interface\MunicipalityServiceInterface;

readonly class MunicipalityService implements MunicipalityServiceInterface
{
    public function __construct(
        private MunicipalityRepositoryInterface $municipalityRepository,
    ) {
    }

    public function updateProposals(Organization $municipality): void
    {
        $this->municipalityRepository->updateProposals($municipality);
    }
}
