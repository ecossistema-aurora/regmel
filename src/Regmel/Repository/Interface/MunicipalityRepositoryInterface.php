<?php

declare(strict_types=1);

namespace App\Regmel\Repository\Interface;

use App\Entity\Organization;

interface MunicipalityRepositoryInterface
{
    public function updateProposals(Organization $organization): void;
}
