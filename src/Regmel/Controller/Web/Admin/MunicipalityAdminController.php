<?php

declare(strict_types=1);

namespace App\Regmel\Controller\Web\Admin;

use App\Controller\Web\Admin\AbstractAdminController;
use App\DocumentService\OrganizationTimelineDocumentService;
use App\Entity\Agent;
use App\Enum\OrganizationTypeEnum;
use App\Regmel\Service\Interface\RegisterServiceInterface;
use App\Service\Interface\AgentServiceInterface;
use App\Service\Interface\EmailServiceInterface;
use App\Service\Interface\InviteServiceInterface;
use App\Service\Interface\OrganizationServiceInterface;
use App\Service\Interface\UserServiceInterface;
use DateTime;
use DateTimeImmutable;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use TypeError;

class MunicipalityAdminController extends AbstractAdminController
{
    public const string VIEW_INVITE_LOGIN = 'regmel/invite/login.html.twig';
    public const string VIEW_INVITE_REGISTER = 'regmel/invite/register.html.twig';

    public const string FORM_INVITE_LOGIN = 'invite-login';
    public const string FORM_INVITE_REGISTER = 'invite-register';

    private const string TIME_TO_EXPIRATION = '+24 hours';

    public function __construct(
        private readonly OrganizationServiceInterface $organizationService,
        private readonly OrganizationTimelineDocumentService $documentService,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
        private readonly AgentServiceInterface $agentService,
        private readonly UserServiceInterface $userService,
        private readonly RegisterServiceInterface $registerService,
        private readonly EmailServiceInterface $emailService,
        private readonly InviteServiceInterface $inviteService,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route('/painel/admin/municipios/{id}/oficio', name: 'regmel_municipality_form_file', methods: ['GET'])]
    public function fileForm(Uuid $id): Response
    {
        $organization = $this->organizationService->get($id);

        $path = $this->getParameter('kernel.project_dir');

        $filePath = "{$path}/storage/regmel/municipality/documents/".$organization->getExtraFields()['form'] ?? 'null';

        if (false === file_exists($filePath)) {
            throw $this->createNotFoundException();
        }

        return new BinaryFileResponse($filePath);
    }

    private function renderOrganizationList(array $municipalities): Response
    {
        return $this->render('regmel/admin/municipality/list.html.twig', [
            'municipalities' => $municipalities,
            'token' => $this->security->getUser() ? $this->jwtManager->create($this->security->getUser()) : null,
        ], parentPath: '');
    }

    #[Route('/painel/admin/municipios', name: 'admin_regmel_municipality_list', methods: ['GET'])]
    public function list(): Response
    {
        $user = $this->security->getUser();

        $agents = $user->getAgents();

        if ($agents->isEmpty()) {
            $this->addFlash('error', $this->translator->trans('user_associated'));

            return $this->redirectToRoute('admin_dashboard');
        }

        $municipalities = $this->organizationService->getMunicipalitiesByAgents($agents);

        return $this->render('regmel/admin/municipality/list.html.twig', [
            'municipalities' => $municipalities,
            'token' => $this->jwtManager->create($user),
            'context_title' => 'my_municipalities',
        ], parentPath: '');
    }

    #[Route('/painel/admin/municipios/{id}', name: 'admin_regmel_municipality_details', methods: ['GET'])]
    public function details(Uuid $id): Response
    {
        $municipality = $this->organizationService->findOneBy([
            'id' => $id,
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
        ]);

        $timeline = $this->documentService->getEventsByEntityId($id);

        return $this->render('regmel/admin/municipality/details.html.twig', [
            'municipality' => $municipality,
            'timeline' => $timeline,
        ], parentPath: '');
    }

    #[Route('/municipios/convites/{token}/aceitar', name: 'accept_municipality_invitation', methods: ['GET', 'POST'])]
    public function acceptInvitation(string $token, Request $request): Response
    {
        $invite = $this->inviteService->findOneBy(['token' => $token]);

        if (null === $invite || $invite->getExpirationAt() < new DateTime()) {
            $this->addFlashError('Convite inválido');

            return $this->redirectToRoute('homepage');
        }

        if (null !== $this->getUser()) {
            $mainAgent = $this->agentService->getMainAgentByEmail($this->getUser()->getEmail());

            $this->inviteService->confirm($invite, $mainAgent);

            $this->addFlashSuccess('Convite aceito');

            return $this->redirectToRoute('admin_regmel_municipality_details', [
                'id' => $invite->getHost()->getId()->toRfc4122(),
            ]);
        }

        if (Request::METHOD_GET === $request->getMethod()) {
            if (null === $invite->getGuest()) {
                return $this->render(self::VIEW_INVITE_REGISTER, [
                    'form_id' => self::FORM_INVITE_REGISTER,
                    'id' => $invite->getHost()->getId()->toRfc4122(),
                ], parentPath: '');
            }

            return $this->render(self::VIEW_INVITE_LOGIN, [
                'form_id' => self::FORM_INVITE_LOGIN,
                'id' => $invite->getHost()->getId()->toRfc4122(),
            ], parentPath: '');
        }

        $type = $request->get('type');

        if ('login' === $type) {
            $email = $request->get('email');
            $password = $request->get('password');

            $user = $this->userService->findOneBy(['email' => $email]);

            if (false === $this->userService->authenticate($user, $password)) {
                $this->addFlashError('Credências invalidas');

                return $this->render(self::VIEW_INVITE_LOGIN, [
                    'form_id' => self::FORM_INVITE_LOGIN,
                    'id' => $invite->getHost()->getId()->toRfc4122(),
                ], parentPath: '');
            }

            $agent = $user->getAgents()->filter(fn (Agent $agent) => true === $agent->isMain())->first();

            $this->inviteService->confirm($invite, $agent);
        }

        if ('register' === $type) {
            $user = $this->registerService->saveUser([
                'id' => Uuid::v4(),
                'firstname' => $request->get('firstname'),
                'lastname' => $request->get('lastname'),
                'email' => $request->get('email'),
                'password' => $request->get('password'),
                'extraFields' => [
                    'cpf' => $request->get('cpf'),
                    'cargo' => $request->get('position'),
                ],
            ]);

            $agent = $user->getAgents()->filter(fn (Agent $agent) => true === $agent->isMain())->first();

            $this->inviteService->confirm($invite, $agent);
        }

        $this->addFlashSuccess('Convite aceito');

        return $this->redirectToRoute('admin_regmel_municipality_details', [
            'id' => $invite->getHost()->getId()->toRfc4122(),
        ]);
    }

    #[Route('/painel/admin/municipios/{id}/convidar-agente', name: 'admin_regmel_municipality_invite_agent', methods: ['POST'])]
    public function inviteAgent(Uuid $id, Request $request): Response
    {
        $name = $request->request->get('name');
        $email = $request->request->get('email');

        $error = [];

        if (false === is_string($name)) {
            $error[] = $this->translator->trans('view.authentication.error.first_name_length');
        }

        if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error[] = $this->translator->trans('view.authentication.error.invalid_email');
        }

        if ([] === $error) {
            $municipality = $this->organizationService->findOneBy([
                'id' => $id,
                'type' => OrganizationTypeEnum::MUNICIPIO->value,
            ]);

            $agent = $this->agentService->getMainAgentByEmail($email);

            $expirationAt = new DateTimeImmutable(self::TIME_TO_EXPIRATION);

            $invite = $this->inviteService->create([
                'id' => Uuid::v4(),
                'guest' => $agent,
                'host' => $municipality,
                'token' => Uuid::v4(),
                'expirationAt' => $expirationAt,
            ]);

            $confirmationUrl = $this->urlGenerator->generate('accept_municipality_invitation', ['token' => $invite->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

            $this->emailService->sendTemplatedEmail(
                [$email],
                $this->translator->trans('invite_to_municipality'),
                '_emails/agent-invitation.html.twig',
                [
                    'name' => $name,
                    'municipality' => $municipality->getName(),
                    'confirmationUrl' => $confirmationUrl,
                ]
            );

            $this->addFlash('success', $this->translator->trans('invite_sent'));
        }

        foreach ($error as $errorMessage) {
            $this->addFlash('error', $errorMessage);
        }

        return $this->redirectToRoute('admin_regmel_municipality_details', ['id' => $id->toRfc4122()]);
    }

    #[Route('/painel/admin/municipios/{id}/editar', name: 'admin_regmel_municipality_edit', methods: ['GET', 'POST'])]
    public function edit(Uuid $id, Request $request): Response
    {
        try {
            $organization = $this->organizationService->get($id);
        } catch (Exception $exception) {
            $this->addFlashError($exception->getMessage());

            return $this->redirectToRoute('admin_organization_list');
        }

        if ($request->isMethod('GET')) {
            return $this->renderOrganizationList(
                $this->organizationService->findBy(['type' => OrganizationTypeEnum::MUNICIPIO->value])
            );
        }

        $this->validCsrfToken('edit-organization', $request);

        try {
            $this->organizationService->update($id, [
                'name' => $request->get('name'),
                'description' => $request->get('description'),
                'extraFields' => array_intersect_key($request->request->all(), array_flip(
                    [
                        'cnpj',
                        'site',
                        'phone',
                        'email',
                        'tipo',
                        'companyName',
                    ]
                )),
            ]);

            $this->addFlashSuccess($this->translator->trans('view.organization.message.updated'));
        } catch (TypeError|Exception $exception) {
            $this->addFlashError($exception->getMessage());

            return $this->list();
        }

        return $this->redirectToRoute('admin_regmel_municipality_list');
    }
}
