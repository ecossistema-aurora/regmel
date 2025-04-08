<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\AccountEvent;
use App\Entity\User;
use App\Enum\AccountEventTypeEnum;
use App\Enum\UserStatusEnum;
use App\Exception\AccountEvent\ExpiredTokenException;
use App\Exception\AccountEvent\InvalidTokenException;
use App\Repository\Interface\AccountEventRepositoryInterface;
use App\Service\Interface\AccountEventServiceInterface;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class AccountEventService extends AbstractEntityService implements AccountEventServiceInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager,
        private AccountEventRepositoryInterface $accountEventRepository,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private Security $security,
    ) {
        parent::__construct($this->security);
    }

    public function confirmAccount(string $token): void
    {
        $accountEvent = $this->entityManager->getRepository(AccountEvent::class)->findOneBy(['token' => $token]);

        if (null === $accountEvent || $accountEvent->getType() !== AccountEventTypeEnum::ACTIVATION->value) {
            throw new InvalidTokenException();
        }

        $user = $this->entityManager->find(User::class, $accountEvent->getUser()->getId()->toRfc4122());

        if ($accountEvent->getExpirationAt() < new DateTimeImmutable()) {
            $this->sendConfirmationEmail($user);

            throw new ExpiredTokenException();
        }

        $user->setStatus(UserStatusEnum::ACTIVE->value);

        $this->entityManager->remove($accountEvent);
        $this->entityManager->flush();
    }

    public function sendConfirmationEmail(User $user): void
    {
        $token = Uuid::v4();

        $confirmationUrl = $this->urlGenerator->generate('web_account_event_confirmation', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        $expirationAt = new DateTimeImmutable('+24 hours');

        $email = (new TemplatedEmail())
            ->from('hello@example.com')
            ->to($user->getEmail())
            ->subject($this->translator->trans('account_confirmation'))
            ->htmlTemplate('_emails/account-event/account-confirmation.html.twig')
            ->context([
                'first_name' => $user->getFirstName(),
                'confirmation_link' => $confirmationUrl,
                'expiration_date' => $expirationAt,
            ]);

        $this->mailer->send($email);

        $this->create([
            'id' => Uuid::v4(),
            'user' => $user,
            'type' => AccountEventTypeEnum::ACTIVATION->value,
            'token' => $token,
            'expirationAt' => $expirationAt,
        ]);
    }

    public function create(array $data): AccountEvent
    {
        $accountEvent = new AccountEvent();

        $accountEvent->setId($data['id']);
        $accountEvent->setUser($data['user']);
        $accountEvent->setType($data['type']);
        $accountEvent->setToken($data['token']);
        $accountEvent->setExpirationAt($data['expirationAt']);

        $this->accountEventRepository->save($accountEvent);

        return $accountEvent;
    }
}
