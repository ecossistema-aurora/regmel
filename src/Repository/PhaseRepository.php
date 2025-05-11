<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Phase;
use App\Repository\Interface\PhaseRepositoryInterface;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;

class PhaseRepository extends AbstractRepository implements PhaseRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Phase::class);
    }

    public function save(Phase $phase): Phase
    {
        $this->getEntityManager()->persist($phase);
        $this->getEntityManager()->flush();

        return $phase;
    }

    public function findCurrentPhase(DateTime $currentDate): ?Phase
    {
        return $this->createQueryBuilder('p')
            ->where('p.startDate <= :currentDate')
            ->andWhere('p.endDate >= :currentDate')
            ->andWhere('p.status = true')
            ->setParameter('currentDate', $currentDate)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
