<?php

declare(strict_types=1);

namespace App\Regmel\Controller\Web;

use App\Regmel\Service\RegisterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    public function __construct(
        private RegisterService $registerService
    ) {
    }

    #[Route('/cadastro/municipio', name: 'regmel_register_city', methods: ['GET', 'POST'])]
    public function registerCity(Request $request): Response
    {
        if (true === $request->isMethod(Request::METHOD_POST)) {
            $this->registerService->save();
        }

        // cadastrar o usuario
        // cadastrar o agente
        // criar a organizacao/municipio (setando o agente como createdBy)
        // add essa organizacao dentro da oportunidade X

        return $this->render('regmel/register/city.html.twig');
    }

    #[Route('/cadastro/empresa', name: 'regmel_register_company', methods: ['GET', 'POST'])]
    public function registerCompany(): Response
    {
        return $this->render('regmel/register/company.html.twig');
    }
}
