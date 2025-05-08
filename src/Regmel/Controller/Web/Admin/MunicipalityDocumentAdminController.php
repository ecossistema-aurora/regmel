<?php

declare(strict_types=1);

namespace App\Regmel\Controller\Web\Admin;

use App\Controller\Web\Admin\AbstractAdminController;
use App\Entity\Organization;
use App\Enum\OrganizationTypeEnum;
use App\Enum\RegionEnum;
use App\Enum\UserRolesEnum;
use App\Exception\UnableCreateFileException;
use App\Regmel\Service\Interface\MunicipalityDocumentServiceInterface;
use App\Service\Interface\OrganizationServiceInterface;
use App\Service\Interface\StateServiceInterface;
use Exception;
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
use ZipArchive;

class MunicipalityDocumentAdminController extends AbstractAdminController
{
    public function __construct(
        private readonly OrganizationServiceInterface $organizationService,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly Security $security,
        private readonly StateServiceInterface $stateService,
        private readonly MunicipalityDocumentServiceInterface $municipalityDocumentService,
    ) {
    }

    private function renderOrganizationList(array $municipalities): Response
    {
        return $this->render('regmel/admin/municipality/documents.html.twig', [
            'municipalities' => $municipalities,
            'token' => $this->security->getUser() ? $this->jwtManager->create($this->security->getUser()) : null,
        ], parentPath: '');
    }

    #[Route('/painel/admin/municipios/{id}/oficio', name: 'regmel_municipality_document_file', methods: ['GET'])]
    public function fileForm(Uuid $id): Response
    {
        $organization = $this->organizationService->get($id);
        $this->denyAccessUnlessGranted('get_form', $organization);

        $filePath = $this->getDocumentPath($organization->getExtraFields()['form'] ?? 'null');

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException();
        }

        return new BinaryFileResponse($filePath);
    }

    #[IsGranted(new Expression('
        is_granted("'.UserRolesEnum::ROLE_ADMIN->value.'") or
        is_granted("'.UserRolesEnum::ROLE_MANAGER->value.'")
    '), statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    #[Route('/painel/admin/municipios-documentos', name: 'admin_regmel_municipality_document_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $filterRegion = $request->query->get('region');
        $filterState = $request->query->get('state');

        $regions = RegionEnum::cases();
        $states = $this->stateService->findBy(['region' => $filterRegion]);

        $municipalities = array_map(function (Organization $organization) {
            $extraFields = $organization->getExtraFields();

            $organization->addExtraField(
                'filepath',
                $this->getDocumentPath($extraFields['form'] ?? 'null')
            );

            $organization->addExtraField(
                'status',
                $extraFields['status'] ?? 'awaiting'
            );

            return $organization;
        }, $this->organizationService->findBy([
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
        ]));

        return $this->render('regmel/admin/municipality/documents.html.twig', [
            'municipalities' => $municipalities,
            'regions' => $regions,
            'states' => $states,
            'token' => $this->security->getUser() ? $this->jwtManager->create($this->security->getUser()) : null,
        ], parentPath: '');
    }

    private function getDocumentPath(string $file): string
    {
        $path = $this->getParameter('kernel.project_dir');

        return "{$path}/storage/regmel/municipality/documents/{$file}";
    }

    #[IsGranted(new Expression('
        is_granted("'.UserRolesEnum::ROLE_ADMIN->value.'") or
        is_granted("'.UserRolesEnum::ROLE_MANAGER->value.'")
    '), statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    #[Route('/painel/admin/municipios-documentos', name: 'admin_regmel_municipality_document_review', methods: ['POST'])]
    public function reviewDocument(): Response
    {
        $municipalities = array_map(function (Organization $organization) {
            $extraFields = $organization->getExtraFields();

            $organization->addExtraField(
                'filepath',
                $this->getDocumentPath($extraFields['form'] ?? 'null')
            );

            $organization->addExtraField(
                'status',
                $extraFields['status'] ?? 'awaiting'
            );

            return $organization;
        }, $this->organizationService->findBy([
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
        ]));

        return $this->renderOrganizationList($municipalities);
    }

    #[IsGranted(new Expression('
        is_granted("'.UserRolesEnum::ROLE_ADMIN->value.'") or
        is_granted("'.UserRolesEnum::ROLE_MANAGER->value.'")
    '), statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    #[Route('/painel/admin/municipios/{id}/document/decision', name: 'admin_municipality_document_decision', methods: ['POST'])]
    public function handleDocumentDecision(Uuid $id, Request $request): Response
    {
        $approved = $request->request->getBoolean('approved');
        $reason = $request->request->get('reason');

        try {
            if (true === empty(trim($reason))) {
                $this->addFlash('error', 'O motivo é obrigatório');

                return $this->redirectToRoute('admin_regmel_municipality_document_review');
            }

            $this->municipalityDocumentService->decision($id, $approved, $reason);

            return $this->redirectToRoute('admin_regmel_municipality_document_review');
        } catch (Exception $e) {
            $this->addFlash('error', 'Erro ao submeter revisão do termo');
        }

        return $this->redirectToRoute('admin_regmel_municipality_document_review');
    }

    #[IsGranted(new Expression('
        is_granted("'.UserRolesEnum::ROLE_ADMIN->value.'") or
        is_granted("'.UserRolesEnum::ROLE_MANAGER->value.'")
    '), statusCode: self::ACCESS_DENIED_RESPONSE_CODE)]
    #[Route('/painel/admin/municipios-documentos/download', name: 'admin_regmel_municipality_document_download', methods: ['GET'])]
    public function downloadDocuments(): Response
    {
        $zipFileName = sprintf('municipality_documents_%s.zip', date('Y-m-d_H-i-s'));
        $zipFilePath = sprintf('%s/storage/regmel/municipality/documents/%s', $this->getParameter('kernel.project_dir'), $zipFileName);

        $zip = new ZipArchive();

        if (true !== $zip->open($zipFilePath, ZipArchive::CREATE)) {
            throw new UnableCreateFileException();
        }

        $municipalities = $this->organizationService->findBy([
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
        ]);

        foreach ($municipalities as $municipality) {
            $filePath = $this->getDocumentPath($municipality->getExtraFields()['form'] ?? '');

            if (true === file_exists($filePath)) {
                $zip->addFile($filePath, basename($filePath));
            }
        }

        $zip->close();

        $response = new BinaryFileResponse($zipFilePath, headers: ['Content-Type' => 'application/zip']);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $zipFileName);
        $response->deleteFileAfterSend(true);

        return $response;
    }
}
