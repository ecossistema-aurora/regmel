<?php

declare(strict_types=1);

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AbstractVoter extends Voter
{
    protected array $actions = [];

    protected string $class = '';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (false === in_array($attribute, $this->actions)) {
            return false;
        }

        if (true === is_object($subject) && false === $subject instanceof $this->class) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return true;
    }
}
