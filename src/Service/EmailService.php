<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Interface\EmailServiceInterface;
use RuntimeException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

readonly class EmailService implements EmailServiceInterface
{
    private string $fromAddress;

    public function __construct(
        private MailerInterface $mailer,
        private ParameterBagInterface $parameterBag,
        public Environment $twig,
    ) {
        $this->fromAddress = $this->parameterBag->get('app.email.address');
    }

    public function send(
        array $to,
        string $subject,
        string $htmlTemplate,
        array $context = [],
    ): void {
        if (null === $this->mailer) {
            throw new RuntimeException('EmailService was not initialized. Call the EmailService::initialize() first.');
        }

        $this->mailer->send(
            (new TemplatedEmail())
                ->from($this->fromAddress)
                ->to(...$to)
                ->subject($subject)
                ->htmlTemplate('_emails/layout.html.twig')
                ->context(array_merge($context, [
                    'subject' => $subject,
                    'content' => $this->twig->render($htmlTemplate, $context),
                ]))
        );
    }
}
