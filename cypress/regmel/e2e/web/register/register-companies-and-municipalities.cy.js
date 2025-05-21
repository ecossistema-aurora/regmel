import '../../../support/commands';

describe('Cadastro de Municípios e Empresas', () => {
    const municipios = [
        { nome: 'Município Norte', estado: 'Amazonas', cidade: 'Manaus', site: 'https://norte.gov.br' },
        { nome: 'Município Nordeste', estado: 'Bahia', cidade: 'Salvador', site: 'https://nordeste.gov.br' },
        { nome: 'Município Centro-Oeste', estado: 'Goiás', cidade: 'Goiânia', site: 'https://centrooeste.gov.br' },
        { nome: 'Município Sudeste', estado: 'São Paulo', cidade: 'São Paulo', site: 'https://sudeste.gov.br' },
        { nome: 'Município Sul', estado: 'Paraná', cidade: 'Curitiba', site: 'https://sul.gov.br' },
    ];

    const empresas = [
        { nome: 'Empresa Norte', site: 'https://empresa-norte.com' },
        { nome: 'Empresa Nordeste', site: 'https://empresa-nordeste.com' },
        { nome: 'Empresa Centro-Oeste', site: 'https://empresa-centrooeste.com' },
        { nome: 'Empresa Sudeste', site: 'https://empresa-sudeste.com' },
        { nome: 'Empresa Sul', site: 'https://empresa-sul.com' },
    ];

    municipios.forEach((municipio, index) => {
        it(`Cadastra o município ${municipio.nome}`, () => {
            const timestamp = Date.now();
            const randomEmail = `municipio${index}${timestamp}@teste.com`;

            cy.visit('/cadastro/municipio');
            cy.get('input[name="firstname"]').type('João');
            cy.get('input[name="lastname"]').type('Silva');
            cy.get('input[name="position"]').type('Diretor');
            cy.gerarCPF().then((cpf) => {
                cy.get('input[name="cpf"]').type(cpf);
            });
            cy.get('input[name="userEmail"]').type(randomEmail);
            cy.get('input[name="password"]').type('Aurora@2024');
            cy.get('input[name="confirm_password"]').type('Aurora@2024');

            cy.get('#state + .ts-wrapper').click().contains(municipio.estado).click();
            cy.wait(2000);
            cy.get('#city + .ts-wrapper').click().type(municipio.cidade).contains(municipio.cidade).click();
            cy.wait(2000);

            cy.get('input[name="site"]').type(municipio.site);
            cy.get('input[name="phone"]').type('11999999999');
            cy.get('input[name="email"]').type(randomEmail);

            cy.get('input[name="hasHousingExperience"][value="true"]').check({ force: true });
            cy.get('input[name="hasPlhis"][value="false"]').check({ force: true });

            cy.get('input[type=file]').selectFile('./cypress/regmel/fixtures/file.pdf');
            cy.get('input[name="acceptTerms"]').check({ force: true });
            cy.get('input[name="acceptPrivacy"]').check({ force: true });
            cy.get('input[name="acceptImage"]').check({ force: true });

            cy.get('button[type="submit"]').click();
            cy.wait(3000);
        });
    });

    empresas.forEach((empresa, index) => {
        it(`Cadastra a empresa ${empresa.nome}`, () => {
            const timestamp = Date.now();
            const randomEmailEmpresa = `empresa${index}${timestamp}@teste.com`;
            const randomEmailUsuario = `usuario${index}${timestamp}@empresa.com`;

            cy.visit('/cadastro/empresa');
            cy.gerarCNPJ().then((cnpj) => {
                cy.get('input[name="cnpj"]').type(cnpj);
            });
            cy.get('input[name="name"]').type(empresa.nome);
            cy.get('input[name="email"]').type(randomEmailEmpresa);
            cy.get('input[name="site"]').type(empresa.site);
            cy.get('input[name="phone"]').type('11999999999');

            cy.get('input[name="firstname"]').type('João');
            cy.get('input[name="lastname"]').type('Silva');
            cy.get('input[name="userPhone"]').type('11988888888');
            cy.get('input[name="position"]').type('Diretor');
            cy.gerarCPF().then((cpf) => {
                cy.get('input[name="cpf"]').type(cpf);
            });
            cy.get('input[name="userEmail"]').type(randomEmailUsuario);
            cy.get('input[name="password"]').type('Aurora@2024');
            cy.get('input[name="confirm_password"]').type('Aurora@2024');

            cy.get('input[name="acceptTerms"]').check({ force: true });
            cy.get('input[name="acceptPrivacy"]').check({ force: true });
            cy.get('input[name="acceptImage"]').check({ force: true });

            cy.get('button[type="submit"]').click();
            cy.wait(5000);
        });
    });
});
