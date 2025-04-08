<?php

declare(strict_types=1);

namespace App\Regmel\Service;

use App\DTO\OrganizationDto;
use App\DTO\UserDto;
use App\Entity\Organization;
use App\Regmel\Service\Interface\RegisterServiceInterface;
use App\Repository\Interface\OrganizationRepositoryInterface;
use App\Repository\Interface\UserRepositoryInterface;
use App\Service\Interface\AgentServiceInterface;
use App\Service\OrganizationService;
use App\Service\UserService;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Serializer\SerializerInterface;

class RegisterService implements RegisterServiceInterface
{
    public function __construct(
        private readonly OrganizationService $organizationService,
        private readonly UserService $userService,
        private readonly SerializerInterface $serializer,
        private readonly OrganizationRepositoryInterface $organizationRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly AgentServiceInterface $agentService,
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
    ) {
    }

    public function saveOrganization(array $data): Organization
    {
        $organization = $this->organizationService->validateInput($data['organization'], OrganizationDto::class, OrganizationDto::CREATE);
        $user = $this->userService->validateInput($data['user'], UserDto::class, UserDto::CREATE);

        $organizationObj = $this->serializer->denormalize($organization, Organization::class);

        try {
            $userObj = $this->userService->create($user);

            $this->userRepository->beginTransaction();
            $this->userRepository->save($userObj);
            $this->userRepository->commit();

            $agent = $this->agentService->createFromUser($user, $data['user']['extraFields'] ?? null);

            $organizationObj->setOwner($agent);
            $organizationObj->setCreatedBy($agent);
            $this->organizationRepository->save($organizationObj);
        } catch (Exception $exception) {
            $this->userRepository->rollback();
            throw $exception;
        }

        return $organizationObj;
    }
}
