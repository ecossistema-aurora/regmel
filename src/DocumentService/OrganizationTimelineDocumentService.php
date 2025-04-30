<?php

declare(strict_types=1);

namespace App\DocumentService;

use App\Document\AbstractDocument;
use App\Document\InviteTimeline;
use App\Document\OrganizationTimeline;
use App\DocumentService\Interface\TimelineDocumentServiceInterface;
use App\Service\Interface\UserServiceInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Uid\Uuid;

final class OrganizationTimelineDocumentService extends AbstractTimelineDocumentService implements TimelineDocumentServiceInterface
{
    public function __construct(DocumentManager $documentManager, UserServiceInterface $userService)
    {
        parent::__construct($documentManager, OrganizationTimeline::class, $userService);
    }

    public function getAllEventsByOrganizationId(Uuid $id): array
    {
        $events = $this->getEventsByEntityId($id);

        $inviteEvents = $this->documentManager->getRepository(InviteTimeline::class)->findBy(['organizationId' => $id]);

        return array_merge(array_map(function (AbstractDocument $event) {
            if (null === $event->getUserId()) {
                return $event->toArray();
            }

            $user = $this->userService->get(Uuid::fromString($event->getUserId()));
            $event->assignAuthor($user);

            return $event->toArray();
        }, $inviteEvents), $events);
    }
}
