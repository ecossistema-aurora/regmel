<?php

declare(strict_types=1);

namespace App\Event\Invite;

use App\Entity\Invite;
use Symfony\Contracts\EventDispatcher\Event;

class SendInviteEvent extends Event
{
    public const string TITLE = 'Send invite';

    public function __construct(public readonly Invite $invite)
    {
    }
}
