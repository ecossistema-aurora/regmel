<?php

declare(strict_types=1);

namespace App\Regmel\Controller\Web\Admin;

use App\Enum\OrganizationTypeEnum;
use App\Service\Interface\OrganizationServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class CompanyAdminController extends AbstractController
{
    public function __construct(
        private readonly OrganizationServiceInterface $organizationService
    ) {
    }

    #[Route('/painel/admin/empresas', name: 'admin_regmel_company_list', methods: ['GET'])]
    public function list(): Response
    {
        $companies = $this->organizationService->findBy([
            'type' => OrganizationTypeEnum::EMPRESA->value,
        ]);

        return $this->render('regmel/admin/company/list.html.twig', [
            'companies' => $companies,
        ]);
    }

    #[Route('/painel/admin/empresas/{id}', name: 'admin_regmel_company_details', methods: ['GET'])]
    public function details(Uuid $id): Response
    {
        $details = $this->organizationService->findOneBy([
            'id' => $id,
            'type' => OrganizationTypeEnum::EMPRESA->value,
        ]);

        return $this->render('regmel/admin/company/details.html.twig', [
            'details' => $details,
        ]);
    }
}
