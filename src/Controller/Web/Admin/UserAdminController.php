<?php

declare(strict_types=1);

namespace App\Controller\Web\Admin;

use App\Document\UserTimeline;
use App\DocumentService\AuthTimelineDocumentService;
use App\DocumentService\UserTimelineDocumentService;
use App\Security\PasswordHasher;
use App\Service\Interface\AgentServiceInterface;
use App\Service\Interface\UserServiceInterface;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use TypeError;

class UserAdminController extends AbstractAdminController
{
    public function __construct(
        private readonly UserTimelineDocumentService $documentService,
        private readonly UserServiceInterface $service,
        private readonly UserTimeline $userTimeline,
        private readonly AuthTimelineDocumentService $authDocumentService,
        private readonly AgentServiceInterface $agentService,
        private JWTTokenManagerInterface $jwtManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function list(): Response
    {
        $users = $this->service->findAll();

        return $this->render('user/list.html.twig', [
            'users' => $users,
        ]);
    }

    public function timeline(Uuid $id): Response
    {
        $events = $this->documentService->getEventsByEntityId($id);

        $authEvents = $this->authDocumentService->getTimelineLoginByUserId($id);

        return $this->render('user/timeline.html.twig', [
            'user' => $this->service->get($id),
            'events' => $events,
            'authEvents' => $authEvents,
        ]);
    }

    public function accountPrivacy(Uuid $id): Response
    {
        $user = $this->service->get($id);

        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $lastLogin = $this->documentService->getLastLoginByUserId($id);

        $agents = $this->agentService->findBy(['user' => $user]);

        return $this->render('user/account-privacy.html.twig', [
            'user' => $user,
            'lastLogin' => $lastLogin,
            'agents' => $agents,
        ]);
    }

    private function updateUserData($user, Request $request): void
    {
        $userData = [
            'firstname' => $request->request->get('firstname'),
            'lastname' => $request->request->get('lastname'),
            'socialName' => $request->request->get('socialName'),
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password')
                ? PasswordHasher::hash($request->request->get('password'))
                : $user->getPassword(),
        ];

        $this->service->update($user->getId(), $userData);

        if ($uploadedImage = $request->files->get('profileImage')) {
            $this->service->updateImage($user->getId(), $uploadedImage);
        }
    }

    private function updateAgentData(Request $request): void
    {
        if ($agentIdString = $request->request->get('agent')) {
            $agentId = Uuid::fromString($agentIdString);

            $agentData = [
                'name' => $request->request->get('name'),
                'shortBio' => $request->request->get('short_description'),
                'longBio' => $request->request->get('long_description'),
                'extraFields' => [
                    'cargo' => $request->request->get('cargo'),
                    'cpf' => $request->request->get('cpf'),
                ],
            ];

            if ($uploadedImage = $request->files->get('profileImage')) {
                $this->agentService->updateImage($agentId, $uploadedImage);
            }

            $this->agentService->update($agentId, $agentData);
        }
    }

    public function editUserProfile(Uuid $id, Request $request): Response
    {
        $user = $this->service->get($id);

        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $agents = $this->agentService->findBy(['user' => $user]);
        $token = $this->jwtManager->create($user);

        if (!$request->isMethod(Request::METHOD_POST)) {
            return $this->renderEditProfile($user, $agents, $token);
        }

        $this->validCsrfToken('edit_profile', $request);

        try {
            $this->updateUserData($user, $request);
            $this->updateAgentData($request);

            $this->addFlashSuccess($this->translator->trans('view.user.message.updated'));
        } catch (Exception|TypeError $exception) {
            $message = $exception->getMessage();

            if (str_contains($message, 'violates one or more constraints')) {
                $message = $this->translator->trans('provided_violates', domain: 'aurora.regmel');
            }

            $this->addFlashError($message);

            return $this->renderEditProfile($user, $agents, $token, $message);
        }

        return $this->accountPrivacy($user->getId());
    }

    private function renderEditProfile($user, $agents, $token, ?string $error = null): Response
    {
        return $this->render('user/edit-profile.html.twig', [
            'user' => [
                'name' => $user->getName(),
                'id' => $user->getId(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'socialName' => $user->getSocialName(),
                'email' => $user->getEmail(),
                'image' => $user->getImage(),
            ],
            'form_id' => 'edit_profile',
            'agents' => $agents,
            'token' => $token,
            'error' => $error,
        ]);
    }
}
