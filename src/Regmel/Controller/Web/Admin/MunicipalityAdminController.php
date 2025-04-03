<?php

declare(strict_types=1);

namespace App\Regmel\Controller\Web\Admin;

use App\Enum\OrganizationTypeEnum;
use App\Service\Interface\OrganizationServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MunicipalityAdminController extends AbstractController
{
    public function __construct(
        private readonly OrganizationServiceInterface $organizationService
    ) {
    }

    #[Route('/painel/municipios', name: 'admin_regmel_municipality_list', methods: ['GET'])]
    public function list(): Response
    {
        $municipalities = $this->organizationService->findBy([
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
        ]);

        return $this->render('regmel/admin/municipality/list.html.twig', [
            'municipalities' => $municipalities,
        ]);
    }
}
