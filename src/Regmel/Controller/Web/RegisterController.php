<?php

declare(strict_types=1);

namespace App\Regmel\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    #[Route('/cadastro/municipio', name: 'register_city', methods: ['GET', 'POST'])]
    public function registerCity(): Response
    {
        return new Response('Cadastro de Municipio');
    }

    #[Route('/cadastro/empresa', name: 'register_company', methods: ['GET', 'POST'])]
    public function registerCompany(): Response
    {
        return new Response('Cadastro de Empresa');
    }

    #[Route('/cadastro/agente-promotor', name: 'register_promoter', methods: ['GET', 'POST'])]
    public function registerPromoter(): Response
    {
        return new Response('Cadastro de Agente Promotor');
    }
}
