<?php

declare(strict_types=1);

namespace App\Regmel\Service;

use App\DTO\OrganizationDto;
use App\DTO\UserDto;
use App\Entity\Organization;
use App\Regmel\Service\Interface\RegisterServiceInterface;
use App\Repository\Interface\OrganizationRepositoryInterface;
use App\Service\Interface\FileServiceInterface;
use App\Service\OrganizationService;
use App\Service\UserService;
use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\SerializerInterface;

class RegisterService implements RegisterServiceInterface
{
    public function __construct(
        private readonly OrganizationService $organizationService,
        private readonly UserService $userService,
        private readonly SerializerInterface $serializer,
        private readonly OrganizationRepositoryInterface $organizationRepository,
        private readonly FileServiceInterface $fileService,
    ) {
    }

    public function saveOrganization(array $data, ?UploadedFile $uploadedFile = null): Organization
    {
        if (null !== $uploadedFile) {
            $data['organization']['extraFields']['form'] = $this->uploadFile($uploadedFile);
        }

        $organization = $this->organizationService->validateInput($data['organization'], OrganizationDto::class, OrganizationDto::CREATE);
        $user = $this->userService->validateInput($data['user'], UserDto::class, UserDto::CREATE);

        $organizationObj = $this->serializer->denormalize($organization, Organization::class);

        try {
            $userObj = $this->userService->create($user);

            $agent = $userObj->getAgents()->first();

            $organizationObj->setOwner($agent);
            $organizationObj->setCreatedBy($agent);
            $this->organizationRepository->save($organizationObj);
        } catch (Exception $exception) {
            throw $exception;
        }

        return $organizationObj;
    }

    private function uploadFile(UploadedFile $uploadedFile): string
    {
        $pdf = $this->fileService->uploadPDF($uploadedFile, extraPath: '/regmel/municipality/documents');

        return $pdf->getFilename();
    }
}
