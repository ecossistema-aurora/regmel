<?php

declare(strict_types=1);

namespace App\Regmel\Controller\Web;

use App\Controller\Web\AbstractWebController;
use App\Enum\OrganizationTypeEnum;
use App\Exception\ValidatorException;
use App\Regmel\Service\Interface\RegisterServiceInterface;
use App\Security\PasswordHasher;
use App\Service\Interface\CityServiceInterface;
use App\Service\Interface\StateServiceInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegisterController extends AbstractWebController
{
    public const VIEW_CITY = 'regmel/register/city.html.twig';

    public const FORM_CITY = 'register-city';

    public function __construct(
        private readonly RegisterServiceInterface $registerService,
        private readonly StateServiceInterface $stateService,
        private readonly CityServiceInterface $cityService,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/cadastro/municipio', name: 'regmel_register_city', methods: ['GET', 'POST'])]
    public function registerCity(Request $request): Response
    {
        $states = $this->stateService->list();

        if (Request::METHOD_POST !== $request->getMethod()) {
            return $this->render(self::VIEW_CITY, [
                'form_id' => self::FORM_CITY,
                'states' => $states,
            ]);
        }

        $this->validCsrfToken(self::FORM_CITY, $request);

        $errors = [];

        try {
            $this->registerService->saveOrganization(
                $this->createOrganizationDataForMunicipality($request)
            );
        } catch (ValidatorException $exception) {
            $errors = $exception->getConstraintViolationList();
        } catch (Exception $exception) {
            $errors = [$exception->getMessage()];
        }

        if (false === empty($errors)) {
            return $this->render(self::VIEW_CITY, [
                'errors' => $errors,
                'form_id' => self::FORM_CITY,
                'states' => $states,
            ]);
        }

        $this->addFlash('success', $this->translator->trans('view.organization.message.created'));

        return $this->redirectToRoute('web_home_homepage');
    }

    #[Route('/cadastro/empresa', name: 'regmel_register_company', methods: ['GET', 'POST'])]
    public function registerCompany(): Response
    {
        return $this->render('regmel/register/company.html.twig');
    }

    private function createOrganizationDataForMunicipality(Request $request): array
    {
        $city = $this->cityService->get($request->get('city'));

        return [
            'organization' => [
                'id' => Uuid::v4(),
                'name' => $city?->getName(),
                'type' => OrganizationTypeEnum::MUNICIPIO->value,
                'extraFields' => [
                    'cityId' => $city?->getId(),
                    'email' => $request->get('email'),
                    'phone' => $request->get('phone'),
                    'cnpj' => $request->get('cnpj'),
                ],
            ],
            'user' => [
                'id' => Uuid::v4(),
                'firstname' => $request->get('firstname'),
                'lastname' => $request->get('lastname'),
                'email' => $request->get('userEmail'),
                'password' => PasswordHasher::hash($request->get('password')),
                'extraFields' => [
                    'cpf' => $request->get('cpf'),
                    'position' => $request->get('position'),
                ],
            ],
        ];
    }
}
