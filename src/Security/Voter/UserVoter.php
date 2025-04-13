<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use App\Enum\UserRolesEnum;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

final class UserVoter extends AbstractVoter
{
    protected array $actions = [
        'list',
    ];

    protected string $class = User::class;

    private array $allowedRoles = [
        UserRolesEnum::ROLE_ADMIN->value,
    ];

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (true === is_object($subject) && false === $subject instanceof $this->class) {
            return false;
        }

        if ($this->accessDecisionManager->decide($token, $this->allowedRoles)) {
            return true;
        }

        return false;
    }
}
