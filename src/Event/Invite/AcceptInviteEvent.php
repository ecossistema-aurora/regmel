<?php

declare(strict_types=1);

namespace App\Event\Invite;

use App\Entity\Invite;
use Symfony\Contracts\EventDispatcher\Event;

class AcceptInviteEvent extends Event
{
    public const string TITLE = 'Accept invite';

    public function __construct(public readonly Invite $invite)
    {
    }
}
