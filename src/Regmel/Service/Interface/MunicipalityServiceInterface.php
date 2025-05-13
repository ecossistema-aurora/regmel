<?php

declare(strict_types=1);

namespace App\Regmel\Service\Interface;

use App\Entity\Organization;

interface MunicipalityServiceInterface
{
    public function getProposals(Organization $municipality): array;

    public function updateProposals(Organization $municipality): void;
}
