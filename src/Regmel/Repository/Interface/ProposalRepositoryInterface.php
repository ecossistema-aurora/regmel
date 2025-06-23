<?php

declare(strict_types=1);

namespace App\Regmel\Repository\Interface;

interface ProposalRepositoryInterface
{
    public function bulkUpdateStatus(array $proposals, string $statusTo): void;
}
