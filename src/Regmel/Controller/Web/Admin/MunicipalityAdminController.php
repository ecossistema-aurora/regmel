<?php

declare(strict_types=1);

namespace App\Regmel\Controller\Web\Admin;

use App\Controller\Web\Admin\AbstractAdminController;
use App\Enum\OrganizationTypeEnum;
use App\Service\Interface\EmailServiceInterface;
use App\Service\Interface\OrganizationServiceInterface;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use TypeError;

class MunicipalityAdminController extends AbstractAdminController
{
    public function __construct(
        private readonly OrganizationServiceInterface $organizationService,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
        private readonly EmailServiceInterface $emailService,
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
        $municipalities = $this->organizationService->findBy([
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
        ]);

        return $this->renderOrganizationList($municipalities);
    }

    #[Route('/painel/admin/municipios/{id}', name: 'admin_regmel_municipality_details', methods: ['GET'])]
    public function details(Uuid $id): Response
    {
        $municipality = $this->organizationService->findOneBy([
            'id' => $id,
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
        ]);

        return $this->render('regmel/admin/municipality/details.html.twig', [
            'municipality' => $municipality,
        ], parentPath: '');
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

            $this->emailService->sendTemplatedEmail(
                [$email],
                $this->translator->trans('invite_to_municipality'),
                '_emails/agent-invitation.html.twig',
                [
                    'name' => $name,
                    'municipality' => $municipality->getName(),
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
