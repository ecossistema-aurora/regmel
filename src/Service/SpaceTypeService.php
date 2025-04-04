<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\SpaceTypeDto;
use App\Entity\SpaceType;
use App\Exception\SpaceType\SpaceTypeResourceNotFoundException;
use App\Repository\Interface\SpaceTypeRepositoryInterface;
use App\Service\Interface\SpaceTypeServiceInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class SpaceTypeService extends AbstractEntityService implements SpaceTypeServiceInterface
{
    public function __construct(
        private SpaceTypeRepositoryInterface $repository,
        private Security $security,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {
        parent::__construct($this->security, $this->serializer, $this->validator);
    }

    public function create(array $spaceType): SpaceType
    {
        $spaceType = $this->validateInput($spaceType, SpaceTypeDto::class, SpaceTypeDto::CREATE);

        $spaceTypeObj = $this->serializer->denormalize($spaceType, SpaceType::class);

        return $this->repository->save($spaceTypeObj);
    }

    public function get(Uuid $id): SpaceType
    {
        $spaceType = $this->findOneBy(['id' => $id]);

        if (null === $spaceType) {
            throw new SpaceTypeResourceNotFoundException();
        }

        return $spaceType;
    }

    public function list(int $limit = 50, array $params = []): array
    {
        return $this->repository->findBy(
            $params,
            ['name' => 'ASC'],
            $limit
        );
    }

    public function remove(Uuid $id): void
    {
        $spaceType = $this->findOneBy(['id' => $id]);

        if (null === $spaceType) {
            throw new SpaceTypeResourceNotFoundException();
        }

        $this->repository->remove($spaceType);
    }

    private function findOneBy(array $array): ?SpaceType
    {
        return $this->repository->findOneBy($array);
    }

    public function update(Uuid $id, array $spaceType): SpaceType
    {
        $spaceTypeFromDB = $this->get($id);

        $spaceTypeDto = $this->validateInput($spaceType, SpaceTypeDto::class, SpaceTypeDto::UPDATE);

        $spaceTypeObj = $this->serializer->denormalize($spaceTypeDto, SpaceType::class, context: [
            'object_to_populate' => $spaceTypeFromDB,
        ]);

        return $this->repository->save($spaceTypeObj);
    }
}
