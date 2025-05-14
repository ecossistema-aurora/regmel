<?php

declare(strict_types=1);

namespace App\Service\Interface;

interface EmailServiceInterface
{
    public function send(
        array $to,
        string $subject,
        string $htmlTemplate,
        array $context = [],
    ): void;
}
