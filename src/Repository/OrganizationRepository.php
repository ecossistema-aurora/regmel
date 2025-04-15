<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Organization;
use App\Enum\OrganizationTypeEnum;
use App\Repository\Interface\OrganizationRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

class OrganizationRepository extends AbstractRepository implements OrganizationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    public function save(Organization $organization): Organization
    {
        $this->getEntityManager()->persist($organization);
        $this->getEntityManager()->flush();

        return $organization;
    }

    public function findOneById(string $id): ?Organization
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findMunicipalitiesByAgents(iterable $agents): array
    {
        if (empty($agents)) {
            return [];
        }

        return $this->createQueryBuilder('o')
            ->leftJoin('o.agents', 'a')
            ->where('a IN (:agents)')
            ->andWhere('o.type = :type')
            ->setParameter('agents', is_array($agents) ? $agents : iterator_to_array($agents))
            ->setParameter('type', OrganizationTypeEnum::MUNICIPIO->value)
            ->getQuery()
            ->getResult();
    }

    public function isOrganizationDuplicate(string $name, string $cityId): bool
    {
        $connection = $this->getEntityManager()->getConnection();

        $sql = <<<SQL
            SELECT COUNT(*) AS total
            FROM organization
            WHERE name = :name
              AND extra_fields->>'cityId' = :cityId
            SQL;

        $statement = $connection->prepare($sql);

        $result = $statement->executeQuery([
            'name' => $name,
            'cityId' => $cityId,
        ]);

        $count = (int) $result->fetchOne();

        return $count > 0;
    }
}
