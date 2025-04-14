<?php

declare(strict_types=1);

namespace App\Service\Interface;

use App\Entity\AccountEvent;
use App\Entity\User;
use DateTimeImmutable;

interface AccountEventServiceInterface
{
    public function create(array $data): AccountEvent;

    public function confirmAccount(string $token): void;

    public function sendConfirmationEmail(User $user): void;

    public function resetPassword(string $token, string $password): void;

    public function sendResetPasswordEmail(string $email): void;

    public function notifyManagerOfNewRegistration(
        string $userName,
        string $organizationName,
        string $organizationType,
        DateTimeImmutable $organizationCreatedAt
    ): void;
}
