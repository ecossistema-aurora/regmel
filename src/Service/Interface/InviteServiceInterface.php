<?php

declare(strict_types=1);

namespace App\Service\Interface;

use App\Entity\Agent;
use App\Entity\Invite;
use Symfony\Component\Uid\Uuid;

interface InviteServiceInterface
{
    public function create(array $data): Invite;

    public function confirm(Invite $invite, Agent $agent): void;

    public function get(Uuid $id): Invite;

    public function findOneBy(array $params = []): ?Invite;
}
