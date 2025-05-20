<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\Initiative;

interface InitiativeRepositoryInterface
{
    public function save(Initiative $initiative): Initiative;

    public function findByFilters(?string $region, ?string $state, ?string $cityName, ?string $status, ?string $anticipation): array;
}
