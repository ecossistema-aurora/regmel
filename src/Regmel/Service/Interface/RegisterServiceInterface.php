<?php

declare(strict_types=1);

namespace App\Regmel\Service\Interface;

use App\Entity\Organization;

interface RegisterServiceInterface
{
    public function saveOrganization(array $data): Organization;
}
