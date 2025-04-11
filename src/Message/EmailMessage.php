<?php

declare(strict_types=1);

namespace App\Message;

readonly class EmailMessage
{
    public function __construct(
        public array $to,
        public string $subject,
        public string $htmlTemplate,
        public array $context = [],
    ) {
    }
}
