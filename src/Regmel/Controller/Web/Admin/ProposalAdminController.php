<?php

declare(strict_types=1);

namespace App\Regmel\Controller\Web\Admin;

use App\Controller\Web\Admin\AbstractAdminController;
use App\Entity\Initiative;
use App\Enum\RegionEnum;
use App\Enum\UserRolesEnum;
use App\Environment\ConfigEnvironment;
use App\Regmel\Service\Interface\ProposalServiceInterface;
use App\Service\Interface\CityServiceInterface;
use App\Service\Interface\InitiativeServiceInterface;
use App\Service\Interface\OrganizationServiceInterface;
use App\Service\Interface\StateServiceInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
        private readonly ConfigEnvironment $configEnvironment,
    ) {
    }

    #[IsGranted(new Expression('
        is_granted("'.UserRolesEnum::ROLE_ADMIN->value.'") or 
        is_granted("'.UserRolesEnum::ROLE_MANAGER->value.'") 
    '), statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    #[Route('/painel/admin/propostas', name: 'admin_regmel_proposal_list', methods: ['GET'])]
    public function list(): Response
    {
        $initiatives = $this->initiativeService->list();

        $env = $this->configEnvironment->aurora();

        $proposals = array_map(function (Initiative $initiative) use ($env) {
            $organization = $initiative->getOrganizationFrom();
            $extraFields = $initiative->getExtraFields();

            return [
                'id' => $initiative->getId()->toRfc4122(),
                'name' => $initiative->getName(),
                'company' => $organization->getName(),
                'city_name' => $extraFields['city_name'] ?? '',
                'region' => $extraFields['region'] ?? '',
                'state' => $extraFields['state'] ?? '',
                'status' => $extraFields['status'] ?? '',
                'quantity_houses' => $extraFields['quantity_houses'] ?? '',
                'area_size' => $extraFields['area_size'] ?? '',
                'created_at' => $initiative->getCreatedAt()->format('d/m/Y'),
                'created_by' => $initiative->getCreatedBy()->getName(),
                'area_option' => $env['proposals']['area_characteristics'][$extraFields['area_characteristic']] ?? '',
                'price_per_house' => $env['variables']['price_per_household'] ?? 1,
                'map_file' => $extraFields['map_file'] ?? '',
                'project_file' => $extraFields['project_file'] ?? '',
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

    #[Route('/painel/admin/propostas/{id}/mapa', name: 'admin_regmel_proposal_mapa', methods: ['GET'])]
    public function getMapaPoligonal(Uuid $id): Response
    {
        $initiative = $this->initiativeService->get($id);

        $filePath = $this->getDocumentPath($initiative->getExtraFields()['map_file'] ?? 'null');

        if (false === file_exists($filePath)) {
            throw $this->createNotFoundException();
        }

        return new BinaryFileResponse($filePath);
    }

    #[Route('/painel/admin/propostas/{id}/projeto', name: 'admin_regmel_proposal_projeto', methods: ['GET'])]
    public function getProjeto(Uuid $id): Response
    {
        $initiative = $this->initiativeService->get($id);

        $fileName = $initiative->getExtraFields()['project_file'] ?? 'null';

        $filePath = $this->getDocumentPath($fileName);

        if (false === file_exists($filePath)) {
            throw $this->createNotFoundException();
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName);

        return $response;
    }

    private function getDocumentPath(string $file): string
    {
        $path = $this->getParameter('kernel.project_dir');

        return "{$path}/storage/regmel/company/documents/{$file}";
    }
}
