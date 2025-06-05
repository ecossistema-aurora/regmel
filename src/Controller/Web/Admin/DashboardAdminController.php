<?php

declare(strict_types=1);

namespace App\Controller\Web\Admin;

use App\Enum\OrganizationTypeEnum;
use App\Enum\UserRolesEnum;
use App\Service\Interface\AgentServiceInterface;
use App\Service\Interface\EventServiceInterface;
use App\Service\Interface\InitiativeServiceInterface;
use App\Service\Interface\InscriptionOpportunityServiceInterface;
use App\Service\Interface\OpportunityServiceInterface;
use App\Service\Interface\OrganizationServiceInterface;
use App\Service\Interface\SpaceServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class DashboardAdminController extends AbstractAdminController
{
    public function __construct(
        readonly private AgentServiceInterface $agentService,
        readonly private OpportunityServiceInterface $opportunityService,
        readonly private EventServiceInterface $eventService,
        readonly private SpaceServiceInterface $spaceService,
        readonly private InitiativeServiceInterface $initiativeService,
        readonly private InscriptionOpportunityServiceInterface $inscriptionService,
        readonly private OrganizationServiceInterface $organizationService,
    ) {
    }

    public function index(): Response
    {
        $user = $this->getUser();

        if (
            true === in_array(UserRolesEnum::ROLE_COMPANY->value, $user->getRoles())
            && 1 === $user->getAgents()->count()
        ) {
            $companies = $this->organizationService->getCompaniesByAgents($user->getAgents());

            if (1 === count($companies)) {
                $company = reset($companies);

                return $this->redirectToRoute('admin_regmel_company_details', ['id' => $company->getId()]);
            }
        }

        if (
            true === in_array(UserRolesEnum::ROLE_MUNICIPALITY->value, $user->getRoles())
            && 1 === $user->getAgents()->count()
        ) {
            $cities = $this->organizationService->getMunicipalitiesByAgents($user->getAgents());

            if (1 === count($cities)) {
                $city = reset($cities);

                return $this->redirectToRoute('admin_regmel_municipality_details', ['id' => $city->getId()]);
            }
        }

        $recentRegistrations = $this->inscriptionService->findRecentByUser($user->getId());
        $createdBy = $this->agentService->getAgentsFromLoggedUser()[0];

        $totalAgents = $this->agentService->count($user);
        $totalUsers = $this->agentService->count();
        $totalOpportunities = $this->opportunityService->count($createdBy);
        $totalEvents = $this->eventService->count($createdBy);
        $totalSpaces = $this->spaceService->count($createdBy);
        $totalOrganizations = $this->organizationService->count($createdBy);
        $totalInitiatives = $this->initiativeService->count($createdBy);
        $totalCities = count($this->organizationService->findBy([
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
        ]));
        $totalCompanies = count($this->organizationService->findBy([
            'type' => OrganizationTypeEnum::EMPRESA->value,
        ]));

        $totals = [
            'totalUsers' => $totalUsers,
            'totalAgents' => $totalAgents,
            'totalOpportunities' => $totalOpportunities,
            'totalEvents' => $totalEvents,
            'totalSpaces' => $totalSpaces,
            'totalInitiatives' => $totalInitiatives,
            'totalCities' => $totalCities,
            'totalCompanies' => $totalCompanies,
            'totalOrganizations' => $totalOrganizations,
        ];

        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'recentRegistrations' => $recentRegistrations,
            'totals' => $totals,
        ]);
    }
}
