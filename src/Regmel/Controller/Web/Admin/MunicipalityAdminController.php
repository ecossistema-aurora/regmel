<?php

declare(strict_types=1);

namespace App\Regmel\Controller\Web\Admin;

use App\Controller\Web\Admin\AbstractAdminController;
use App\Enum\OrganizationTypeEnum;
use App\Service\Interface\OrganizationServiceInterface;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
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
    ) {
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
        $details = $this->organizationService->findOneBy([
            'id' => $id,
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
        ]);

        return $this->render('regmel/admin/municipality/details.html.twig', [
            'details' => $details,
        ]);
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
