<?php

declare(strict_types=1);

namespace App\Regmel\Repository;

use App\Regmel\Repository\Interface\ProposalRepositoryInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;

final class ProposalRepository implements ProposalRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function bulkUpdateStatus(array $proposals, string $statusTo): void
    {
        $sql = <<<SQL
                UPDATE initiative
                SET extra_fields = jsonb_set(
                    extra_fields::jsonb,
                    '{status}',
                    to_jsonb(:statusTo::text)
                ),
                updated_at = now()
                WHERE id IN (:ids)
            SQL;

        $this->entityManager->getConnection()->executeStatement(
            $sql,
            [
                'statusTo' => $statusTo,
                'ids' => $proposals,
            ],
            ['ids' => ArrayParameterType::STRING],
        );
    }
}
