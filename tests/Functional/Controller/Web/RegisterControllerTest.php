<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Web;

use App\Entity\Agent;
use App\Entity\Organization;
use App\Enum\OrganizationTypeEnum;
use App\Regmel\Controller\Web\RegisterController;
use App\Regmel\Service\RegisterService;
use App\Service\Interface\CityServiceInterface;
use App\Service\Interface\StateServiceInterface;
use App\Tests\AbstractWebTestCase;
use App\Tests\Utils\CsrfTokenHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RegisterControllerTest extends AbstractWebTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testCreateOrganizationWithFormData(): void
    {
        $createUrl = $this->router->generate('regmel_register_city');
        $request = $this->client->request(Request::METHOD_GET, $createUrl);

        $token = $request->filter('input[name="token"]')->attr('value');

        $formData = [
            'token' => $token,
            'firstname' => 'Bonitão',
            'lastname' => 'das Tapiocas',
            'userEmail' => 'bonitao@example.com',
            'password' => '!@#tdsyfd$%hhjghj',
            'cpf' => '1111111111111',
            'position' => 'Bonzão',
            'city' => '42b2c26f-85f0-44db-a7f9-758788d4e9a8',
            'cnpj' => '111111111111111111',
            'phone' => '9876541230',
            'email' => 'quixelo@quixelo.ce.gov.br',
        ];

        $this->client->request(Request::METHOD_POST, $createUrl, $formData);

        $listUrl = $this->router->generate('web_organization_list');

        $this->assertResponseRedirects($listUrl, Response::HTTP_FOUND);

        $organizations = $this->entityManager->getRepository(Organization::class)->findBy([
            'name' => 'Quixelô',
            'type' => OrganizationTypeEnum::MUNICIPIO->value,
        ]);

        self::assertCount(1, $organizations);

        $agents = $this->entityManager->getRepository(Agent::class)->findBy([
            'name' => 'Bonitão das Tapiocas',
        ]);

        self::assertCount(1, $agents);
    }

    public function testCreateOrganizationWithInvalidFormData(): void
    {
        $createUrl = $this->router->generate('regmel_register_city');

        $request = $this->client->request(Request::METHOD_GET, $createUrl);

        $form = $request->selectButton('Salvar')->form([]);

        $this->client->submit($form);

        $this->assertSelectorTextContains('.toast-body', 'Nome: This value should not be blank.');
    }

    public function testCreateOrganizationThrowGeneralException(): void
    {
        $serviceStub = $this->createMock(RegisterService::class);
        $serviceStub->expects($this->once())
            ->method('saveOrganization')
            ->willThrowException(new Exception('Generic error'));

        $formData = [
            'token' => CsrfTokenHelper::getValidToken(RegisterController::FORM_CITY, $this->client),
            'firstname' => 'Bonitão',
            'lastname' => 'das Tapiocas',
            'userEmail' => 'bonitao@example.com',
            'password' => '!@#tdsyfd$%hhjghj',
            'cpf' => '1111111111111',
            'position' => 'Bonzão',
            'city' => '42b2c26f-85f0-44db-a7f9-758788d4e9a8',
            'cnpj' => '111111111111111111',
            'phone' => '9876541230',
            'email' => 'quixelo@quixelo.ce.gov.br',
        ];

        $translator = self::getContainer()->get('translator');
        $stateService = self::getContainer()->get(StateServiceInterface::class);
        $cityService = self::getContainer()->get(CityServiceInterface::class);
        $controller = new RegisterController($serviceStub, $stateService, $cityService, $translator);
        $controller->setContainer($this->client->getContainer());

        $request = new Request(request: $formData);
        $request->setMethod(Request::METHOD_POST);

        $this->expectException(Exception::class);
        $controller->registerCity($request);

        $this->expectExceptionMessage('Generic Error');
    }
}
