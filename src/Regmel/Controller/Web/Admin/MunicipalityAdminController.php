<?php

declare(strict_types=1);

namespace App\Regmel\Controller\Web\Admin;

use App\Controller\Web\Admin\AbstractAdminController;
use App\DocumentService\OrganizationTimelineDocumentService;
use App\Enum\OrganizationTypeEnum;
use App\Enum\UserRolesEnum;
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
        private readonly OrganizationTimelineDocumentService $documentService,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
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

        if (true === in_array(UserRolesEnum::ROLE_ADMIN->value, $user->getRoles())) {
            $municipalities = $this->organizationService->findBy([
                'type' => OrganizationTypeEnum::MUNICIPIO->value,
            ]);

            return $this->render('regmel/admin/municipality/list.html.twig', [
                'municipalities' => $municipalities,
                'token' => $this->jwtManager->create($user),
                'context_title' => 'my_municipalities',
            ], parentPath: '');
        }

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

        $createdById = $municipality->getCreatedBy()->getId()->toRfc4122();

        if (null === $municipality) {
            throw $this->createNotFoundException($this->translator->trans('municipality_found'));
        }

        $timeline = $this->documentService->getEventsByEntityId($id);

        return $this->render('regmel/admin/municipality/details.html.twig', [
            'municipality' => $municipality,
            'timeline' => $timeline,
            'createdById' => $createdById,
        ], parentPath: '');
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
                'description' => $request->get('description'),
                'extraFields' => array_merge(
                    $organization->getExtraFields(),
                    array_intersect_key($request->request->all(), array_flip([
                        'site',
                        'telefone',
                        'email',
                    ]))
                ),
            ]);

            if ($uploadedImage = $request->files->get('profileImage')) {
                $this->organizationService->updateImage($id, $uploadedImage);
            }

            $this->addFlashSuccess($this->translator->trans('view.organization.message.updated'));
        } catch (TypeError|Exception $exception) {
            $this->addFlashError($exception->getMessage());

            return $this->list();
        }

        return $this->redirectToRoute('admin_regmel_municipality_list');
    }
}
