<?php

declare(strict_types=1);

namespace App\Controller\Web\Admin;

use App\Document\SpaceTimeline;
use App\DocumentService\SpaceTimelineDocumentService;
use App\Enum\UserRolesEnum;
use App\Service\Interface\ActivityAreaServiceInterface;
use App\Service\Interface\ArchitecturalAccessibilityServiceInterface;
use App\Service\Interface\SpaceServiceInterface;
use App\Service\Interface\TagServiceInterface;
use DateTime;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use TypeError;

class SpaceAdminController extends AbstractAdminController
{
    private const string VIEW_LIST = 'space/list.html.twig';
    private const string VIEW_ADD = 'space/create.html.twig';
    private const string VIEW_EDIT = 'space/edit.html.twig';

    public const string CREATE_FORM_ID = 'add-space';
    public const string EDIT_FORM_ID = 'edit-space';

    public function __construct(
        private readonly SpaceServiceInterface $service,
        private readonly SpaceTimelineDocumentService $documentService,
        private readonly TranslatorInterface $translator,
        private readonly Security $security,
        private readonly SpaceTimeline $spaceTimeline,
        private ArchitecturalAccessibilityServiceInterface $architecturalAccessibilityService,
        private readonly ActivityAreaServiceInterface $activityAreaService,
        private readonly TagServiceInterface $tagService,
    ) {
    }

    #[IsGranted(UserRolesEnum::ROLE_ADMIN->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function list(): Response
    {
        $spaces = $this->service->findBy();

        return $this->render(self::VIEW_LIST, [
            'spaces' => $spaces,
        ]);
    }

    #[IsGranted(UserRolesEnum::ROLE_ADMIN->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function remove(?Uuid $id): Response
    {
        $this->service->remove($id);

        $this->addFlashSuccess($this->translator->trans('view.space.message.deleted'));

        return $this->redirectToRoute('admin_space_list');
    }

    #[IsGranted(UserRolesEnum::ROLE_ADMIN->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function create(Request $request): Response
    {
        if (false === $request->isMethod(Request::METHOD_POST)) {
            return $this->render(self::VIEW_ADD, [
                'form_id' => self::CREATE_FORM_ID,
            ]);
        }

        $this->validCsrfToken(self::CREATE_FORM_ID, $request);

        $name = $request->request->get('name');
        $maxCapacity = (int) $request->request->get('maxCapacity');
        $isAccessible = (bool) $request->request->get('isAccessible');

        $space = [
            'id' => Uuid::v4(),
            'name' => $name,
            'maxCapacity' => $maxCapacity,
            'isAccessible' => $isAccessible,
            'createdBy' => $this->security->getUser()->getAgents()->getValues()[0]->getId(),
        ];

        try {
            $this->service->create($space);
            $this->addFlashSuccess($this->translator->trans('view.space.message.created'));
        } catch (Exception|TypeError $exception) {
            $this->addFlashError($exception->getMessage());

            return $this->render(self::VIEW_ADD, [
                'error' => $exception->getMessage(),
                'form_id' => self::CREATE_FORM_ID,
            ]);
        }

        return $this->redirectToRoute('admin_space_list');
    }

    #[IsGranted(UserRolesEnum::ROLE_ADMIN->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function timeline(Uuid $id): Response
    {
        $events = $this->documentService->getEventsByEntityId($id);

        return $this->render('space/timeline.html.twig', [
            'space' => $this->service->get($id),
            'events' => $events,
        ]);
    }

    #[IsGranted(UserRolesEnum::ROLE_ADMIN->value, statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    public function edit(Uuid $id, Request $request): Response
    {
        try {
            $space = $this->service->get($id);
        } catch (Exception $exception) {
            $this->addFlashError($exception->getMessage());

            return $this->redirectToRoute('admin_space_list');
        }

        if (Request::METHOD_POST !== $request->getMethod()) {
            $accessibilities = $this->architecturalAccessibilityService->list();
            $activityAreaItems = $this->activityAreaService->list();
            $tagItems = $this->tagService->list();

            return $this->render(self::VIEW_EDIT, [
                'space' => $space,
                'form_id' => self::EDIT_FORM_ID,
                'accessibilities' => $accessibilities,
                'activityAreaItems' => $activityAreaItems,
                'tagItems' => $tagItems,
            ]);
        }

        $this->validCsrfToken(self::EDIT_FORM_ID, $request);

        $name = $request->request->get('name');
        $description = $request->request->get('extraFields')['description'] ?? null;
        $date = $request->request->get('date') ?? null;
        $tags = $request->get('tags') ?? [];
        $activityAreas = $request->get('activityAreas') ?? [];

        $dataToUpdate = [
            'name' => $name,
            'description' => $description,
            'tags' => $tags,
            'activityAreas' => $activityAreas,
            'date' => $date ? new DateTime($date) : null,
            'updatedBy' => $this->security->getUser()->getAgents()->getValues()[0]->getId(),
        ];

        try {
            $this->service->update($id, $dataToUpdate);

            $this->addFlashSuccess($this->translator->trans('view.space.message.updated'));

            return $this->redirectToRoute('admin_space_list');
        } catch (TypeError|Exception $exception) {
            $this->addFlashError($exception->getMessage());

            return $this->render(self::VIEW_EDIT, [
                'space' => $space,
                'error' => $exception->getMessage(),
                'form_id' => self::EDIT_FORM_ID,
            ]);
        }
    }
}
