<?php

declare(strict_types=1);

namespace App\Regmel\Repository;

use App\Entity\Organization;
use App\Enum\StatusProposalEnum;
use App\Environment\ConfigEnvironment;
use App\Regmel\Repository\Interface\MunicipalityRepositoryInterface;
use App\Repository\OrganizationRepository;
use Doctrine\Persistence\ManagerRegistry;

class MunicipalityRepository extends OrganizationRepository implements MunicipalityRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly ConfigEnvironment $configEnvironment
    ) {
        parent::__construct($registry);
    }

    public function updateProposals(Organization $organization, StatusProposalEnum $statusFrom, StatusProposalEnum $statusTo): void
    {
        $conn = $this->getEntityManager()->getConnection();

        $env = $this->configEnvironment->aurora();

        $sql = "UPDATE initiative 
            SET 
                organization_to_id = :organizationId::uuid,
                extra_fields = jsonb_set(
                    extra_fields::jsonb, 
                    '{status}', 
                    to_jsonb(:statusTo::text)
                ),
                updated_at = now() AT TIME ZONE :timezone
            WHERE 
                extra_fields->>'city_id' = :cityId
                AND extra_fields->>'status' = :statusFrom";

        $conn->executeStatement($sql, [
            'organizationId' => $organization->getId()->toRfc4122(),
            'cityId' => $organization->getExtraFields()['cityId'],
            'statusFrom' => $statusFrom->value,
            'statusTo' => $statusTo->value,
            'timezone' => $env['default_timezone'],
        ]);
    }
}
