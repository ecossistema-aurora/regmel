<?php

declare(strict_types=1);

namespace App\Regmel\Controller\Web\Admin;

use App\Controller\Web\Admin\AbstractAdminController;
use App\Entity\Initiative;
use App\Enum\RegionEnum;
use App\Enum\UserRolesEnum;
use App\Regmel\Service\Interface\ProposalServiceInterface;
use App\Service\Interface\CityServiceInterface;
use App\Service\Interface\InitiativeServiceInterface;
use App\Service\Interface\OrganizationServiceInterface;
use App\Service\Interface\StateServiceInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;

class ProposalAdminController extends AbstractAdminController
{
    public function __construct(
        private readonly OrganizationServiceInterface $organizationService,
        private readonly ProposalServiceInterface $proposalService,
        private readonly StateServiceInterface $stateService,
        private readonly CityServiceInterface $cityService,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly Security $security,
        private readonly InitiativeServiceInterface $initiativeService,
    ) {
    }

    private function renderCompanyList(array $companies): Response
    {
        return $this->render('regmel/admin/company/list.html.twig', [
            'companies' => $companies,
            'token' => $this->security->getUser() ? $this->jwtManager->create($this->security->getUser()) : null,
        ], parentPath: '');
    }

    #[IsGranted(new Expression('
    is_granted("'.UserRolesEnum::ROLE_ADMIN->value.'") or 
    is_granted("'.UserRolesEnum::ROLE_MANAGER->value.'") 
'), statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    #[Route('/painel/admin/propostas', name: 'admin_regmel_proposal_list', methods: ['GET'])]
    public function list(): Response
    {
        $initiatives = $this->initiativeService->list();

        $proposals = array_map(function (Initiative $initiative) {
            $organization = $initiative->getOrganizationFrom();
            $extraFields = $initiative->getExtraFields();

            return [
                'companyName' => $organization->getName(),
                'cityName' => $extraFields['city_name'],
                'status' => $extraFields['status'],
                'housesQuantity' => $extraFields['quantity_houses'],
                'totalArea' => $extraFields['area_size'],
            ];
        }, $initiatives);

        return $this->render('regmel/admin/proposal/list.html.twig', [
            'proposals' => $proposals,
        ], parentPath: '');
    }

    #[IsGranted(new Expression('
        is_granted("'.UserRolesEnum::ROLE_COMPANY->value.'")
    '), statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    #[Route('/painel/admin/empresas/{id}/nova-proposta', name: 'admin_regmel_proposal_add', methods: ['GET', 'POST'])]
    public function add(Uuid $id, Request $request): Response
    {
        $user = $this->security->getUser();
        $company = $this->organizationService->get($id);

        if (false === $request->isMethod(Request::METHOD_POST)) {
            $regions = RegionEnum::cases();
            $states = $this->stateService->findBy();
            $cities = $this->cityService->findBy();
            // $opportunities = $this->registerService->findOpportunitiesBy(OrganizationTypeEnum::EMPRESA);

            return $this->render('regmel/admin/proposal/add.html.twig', [
                'states' => $states,
                'cities' => $cities,
                'regions' => $regions,
                'token' => $this->jwtManager->create($user),
                'company' => $company,
                // 'opportunities' => $opportunities,
            ], parentPath: '');
        }

        $this->proposalService->saveProposal(
            $company,
            $request->request->all(),
            $request->files->get('map'),
            $request->files->get('project')
        );

        $this->addFlashSuccess('Pronto, nova proposta enviada');

        return $this->redirectToRoute('admin_regmel_company_list', [
            'id' => $id,
        ]);
    }
}
