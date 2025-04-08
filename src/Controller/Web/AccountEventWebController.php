<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Exception\AccountEvent\ExpiredTokenException;
use App\Exception\AccountEvent\InvalidTokenException;
use App\Service\Interface\AccountEventServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class AccountEventWebController extends AbstractWebController
{
    public function __construct(
        public readonly AccountEventServiceInterface $accountEventService,
    ) {
    }

    public function confirmAccount(
        string $token,
    ): Response {
        try {
            $this->accountEventService->confirmAccount($token);
        } catch (InvalidTokenException|ExpiredTokenException $exception) {
            $this->addFlash('error', $exception->getMessage());

            return $this->redirectToRoute('web_home_homepage');
        }

        return $this->render('account-event/account-confirmed.twig');
    }
}
