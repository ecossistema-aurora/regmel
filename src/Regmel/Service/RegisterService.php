<?php

declare(strict_types=1);

namespace App\Regmel\Service;

use App\Service\UserService;

class RegisterService
{
    public function __construct(
        private readonly UserService $userService
    ) {
    }

    public function save(): void
    {
        var_dump(
            $this->userService->findAll()[0]->getName()
        );
    }
}