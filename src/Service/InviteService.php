<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Agent;
use App\Entity\Invite;
use App\Repository\Interface\InviteRepositoryInterface;
use App\Service\Interface\InviteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class InviteService extends AbstractEntityService implements InviteServiceInterface
{
    public function __construct(
        private InviteRepositoryInterface $repository,
        private Security $security,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct(
            $this->security,
            $this->serializer,
            $this->validator,
            $this->entityManager,
            Invite::class,
        );
    }

    public function create(array $data): Invite
    {
        $invite = new Invite();
        $invite->setId($data['id']);
        $invite->setGuest($data['guest'] ?? null);
        $invite->setHost($data['host']);
        $invite->setToken($data['token']);
        $invite->setExpirationAt($data['expirationAt']);

        return $this->repository->save($invite);
    }

    public function confirm(Invite $invite, Agent $agent): void
    {
        $host = $invite->getHost();
        $guest = $invite->getGuest();

        if (null !== $guest && $guest->getId()->toRfc4122() !== $agent->getId()->toRfc4122()) {
            throw new Exception('Esse convite não é seu... Te manca');
        }

        $host->addAgent($agent);

        $this->entityManager->persist($host);
        $this->entityManager->flush();
    }

    public function get(Uuid $id): Invite
    {
        return $this->repository->find($id);
    }

    public function findOneBy(array $params = []): ?Invite
    {
        return $this->repository->findOneBy($params);
    }
}
