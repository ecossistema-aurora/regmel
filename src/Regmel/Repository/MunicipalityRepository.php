<?php

declare(strict_types=1);

namespace App\Regmel\Repository;

use App\Entity\Organization;
use App\Regmel\Repository\Interface\MunicipalityRepositoryInterface;
use App\Repository\OrganizationRepository;

class MunicipalityRepository extends OrganizationRepository implements MunicipalityRepositoryInterface
{
    public function updateProposals(Organization $organization): void
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "UPDATE initiative 
            SET 
                organization_to_id = :organizationId::uuid,
                extra_fields = jsonb_set(
                    extra_fields::jsonb, 
                    '{status}', 
                    '\"Enviada\"'::jsonb
                )
            WHERE 
                extra_fields->>'city_id' = :cityId
                AND organization_to_id IS NULL
                AND extra_fields->>'status' = 'Sem Adesão do Município'";

        $conn->executeStatement($sql, [
            'organizationId' => $organization->getId()->toRfc4122(),
            'cityId' => $organization->getExtraFields()['cityId'],
        ]);
    }
}
