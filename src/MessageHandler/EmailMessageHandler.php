<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\EmailMessage;
use App\Service\Interface\EmailServiceInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class EmailMessageHandler
{
    public function __construct(
        private EmailServiceInterface $emailService,
    ) {
    }

    public function __invoke(EmailMessage $message): void
    {
        $this->emailService->send(
            $message->to,
            $message->subject,
            $message->htmlTemplate,
            $message->context
        );
    }
}
