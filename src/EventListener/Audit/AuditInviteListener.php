<?php

declare(strict_types=1);

namespace App\EventListener\Audit;

use App\Document\InviteTimeline;
use App\Entity\Invite;
use App\Event\Invite\AcceptInviteEvent;
use App\Event\Invite\RemoveInviteEvent;
use App\Event\Invite\SendInviteEvent;
use DateTime;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\Event;

#[AsEventListener(event: AcceptInviteEvent::class, priority: 1)]
#[AsEventListener(event: SendInviteEvent::class, priority: 1)]
class AuditInviteListener extends AbstractAuditListener
{
    public function __construct(
        protected DocumentManager $documentManager,
        protected RequestStack $requestStack,
        protected Security $security,
    ) {
        parent::__construct($documentManager, $requestStack, $security);
    }

    public function __invoke(Event $event): void
    {
        if ($event instanceof AcceptInviteEvent) {
            $this->acceptInviteEvent($event);
        }

        if ($event instanceof SendInviteEvent) {
            $this->sendInviteEvent($event);
        }

        if ($event instanceof RemoveInviteEvent) {
            $this->removeInviteEvent($event);
        }
    }

    public function acceptInviteEvent(AcceptInviteEvent $event): void
    {
        $this->createInvite($event->invite, AcceptInviteEvent::TITLE);
    }

    private function sendInviteEvent(SendInviteEvent $event): void
    {
        $this->createInvite($event->invite, SendInviteEvent::TITLE);
    }

    private function removeInviteEvent(RemoveInviteEvent $event): void
    {
        $this->createInvite($event->invite, RemoveInviteEvent::TITLE);
    }

    private function createInvite(Invite $invite, string $title): void
    {
        $document = new InviteTimeline();
        $document->setTitle($title);
        $document->setResourceId($invite->getId()->toRfc4122());
        $document->setPriority(0);
        $document->setDatetime(new DateTime());
        $document->setDevice($this->getDevice());
        $document->setPlatform($this->getPlatform());
        $document->setFrom([]);
        $document->setTo([]);

        $this->documentManager->persist($document);
        $this->documentManager->flush();
    }
}
