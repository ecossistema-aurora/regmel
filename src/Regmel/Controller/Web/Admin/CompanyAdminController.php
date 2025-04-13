<?php

declare(strict_types=1);

namespace App\Regmel\Controller\Web\Admin;

use App\Controller\Web\Admin\AbstractAdminController;
use App\DocumentService\OrganizationTimelineDocumentService;
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

class CompanyAdminController extends AbstractAdminController
{
    public function __construct(
        private readonly OrganizationServiceInterface $organizationService,
        private readonly OrganizationTimelineDocumentService $documentService,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
    ) {
    }

    private function renderCompanyList(array $companies): Response
    {
        return $this->render('regmel/admin/company/list.html.twig', [
            'companies' => $companies,
            'token' => $this->security->getUser() ? $this->jwtManager->create($this->security->getUser()) : null,
        ], parentPath: '');
    }

    #[Route('/painel/admin/empresas', name: 'admin_regmel_company_list', methods: ['GET'])]
    public function list(): Response
    {
        $companies = $this->organizationService->findBy([
            'type' => OrganizationTypeEnum::EMPRESA->value,
        ]);

        return $this->renderCompanyList($companies);
    }

    #[Route('/painel/admin/empresas/{id}', name: 'admin_regmel_company_details', methods: ['GET'])]
    public function details(Uuid $id): Response
    {
        $details = $this->organizationService->findOneBy([
            'id' => $id,
            'type' => OrganizationTypeEnum::EMPRESA->value,
        ]);

        $timeline = $this->documentService->getEventsByEntityId($id);

        return $this->render('regmel/admin/company/details.html.twig', [
            'details' => $details,
            'timeline' => $timeline,
        ], parentPath: '');
    }

    #[Route('/painel/admin/empresas/{id}/editar', name: 'admin_regmel_company_edit', methods: ['GET', 'POST'])]
    public function edit(Uuid $id, Request $request): Response
    {
        try {
            $company = $this->organizationService->get($id);
        } catch (Exception $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('admin_regmel_company_list');
        }

        if ($request->isMethod('GET')) {
            return $this->renderCompanyList(
                $this->organizationService->findBy(['type' => OrganizationTypeEnum::EMPRESA->value])
            );
        }

        $this->validCsrfToken('edit-company', $request);

        try {
            $this->organizationService->update($id, [
                'name' => $request->get('name'),
                'description' => $request->get('description'),
                'extraFields' => array_intersect_key($request->request->all(), array_flip(
                    [
                        'cnpj',
                        'site',
                        'telefone',
                        'email',
                        'tipo',
                    ]
                )),
            ]);

            $this->addFlash('success', $this->translator->trans('view.company.message.updated'));
        } catch (TypeError|Exception $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->list();
        }

        return $this->redirectToRoute('admin_regmel_company_list');
    }
}
