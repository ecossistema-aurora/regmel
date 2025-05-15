describe('Confirmação do usuário', () => {
    const municipios = [
        {
            nome: 'Município Norte',
            estado: 'Amazonas',
            cidade: 'Manaus',
            site: 'https://norte.gov.br'
        },
        {
            nome: 'Município Nordeste',
            estado: 'Bahia',
            cidade: 'Salvador',
            site: 'https://nordeste.gov.br'
        }
    ];

    const usuariosCriados = [];

    before(() => {
        municipios.forEach((municipio, index) => {
            const timestamp = Date.now();
            const randomEmail = `municipio${index}_${timestamp}@teste.com`;
            const randomCPF = `${Math.floor(10000000000 + Math.random() * 89999999999)}`;

            usuariosCriados.push({ nome: 'João Silva', email: randomEmail });

            cy.visit('/cadastro/municipio');

            cy.get('input[name="firstname"]').type('Beatriz');
            cy.get('input[name="lastname"]').type('Silva');
            cy.get('input[name="position"]').type('Diretor');
            cy.get('input[name="cpf"]').type(randomCPF);
            cy.get('input[name="userEmail"]').type(randomEmail);
            cy.get('input[name="password"]').type('Aurora@2024');
            cy.get('input[name="confirm_password"]').type('Aurora@2024');

            cy.get('#state + .ts-wrapper').click().contains(municipio.estado).click();
            cy.wait(1000);
            cy.get('#city + .ts-wrapper').click().type(municipio.cidade).contains(municipio.cidade).click();
            cy.wait(1000);

            cy.get('input[name="site"]').type(municipio.site);
            cy.get('input[name="phone"]').type('11999999999');
            cy.get('input[name="email"]').type(randomEmail);

            cy.get('input[name="hasHousingExperience"][value="1"]').check({ force: true });
            cy.get('input[name="hasPlhis"][value="1"]').check({ force: true });

            cy.get('input[type=file]').selectFile('./cypress/regmel/fixtures/file.pdf');
            cy.get('input[name="acceptTerms"]').check({ force: true });
            cy.get('input[name="acceptPrivacy"]').check({ force: true });
            cy.get('input[name="acceptImage"]').check({ force: true });

            cy.get('button[type="submit"]').click();
            cy.wait(3000);
        });
    });

    beforeEach(() => {
        cy.visit('/login');
        cy.get('input[name="email"]').type('admin@regmel.com');
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.get('button[data-cy="submit"]').click();
        cy.wait(1000);
    });

    it('Confirma todos os usuários recém-criados', () => {
        cy.get('a').contains('Usuários').click();

        usuariosCriados.forEach((user) => {
            cy.get('table > tbody').contains('tr', user.email).as('userRow');

            cy.get('@userRow').find('td').eq(3).should('contain', 'Aguardando Confirmar Conta');

            cy.get('@userRow').find('td').last().within(() => {
                cy.get('.dropdown-toggle').click();
                cy.contains('Confirmar Usuário').click();
            });

            cy.get('.modal-body').should('contain', user.email);
            cy.get('a[data-modal-button="confirm-link"]').click();

            cy.get('table > tbody').contains('tr', user.email).find('td').eq(3).should('contain', 'Ativo');
        });
    });
});
