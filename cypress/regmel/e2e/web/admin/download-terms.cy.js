describe('Cadastro de Municípios', () => {
    const municipios = [
        { nome: 'Município Norte', estado: 'Amazonas', cidade: 'Manaus', site: 'https://norte.gov.br' },
        { nome: 'Município Nordeste', estado: 'Bahia', cidade: 'Salvador', site: 'https://nordeste.gov.br' },
    ];

    municipios.forEach((municipio, index) => {
        it(`Cadastra o município ${municipio.nome}`, () => {
            const timestamp = Date.now();
            const randomEmail = `municipio${index}${timestamp}@teste.com`;
            const randomCPF = `${Math.floor(10000000000 + Math.random() * 89999999999)}`;

            cy.visit('/cadastro/municipio');
            cy.get('input[name="firstname"]').type('João');
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

            cy.get('input[type=file]').selectFile('./cypress/regmel/fixtures/file.pdf');

            cy.get('input[name="acceptTerms"]').check({ force: true });
            cy.get('input[name="acceptPrivacy"]').check({ force: true });
            cy.get('input[name="acceptImage"]').check({ force: true });

            cy.get('button[type="submit"]').click();
            cy.wait(1000);
        });
    });
});

describe('Fluxo de Exportação de Termos de Adesão como ZIP', () => {
    beforeEach(() => {
        cy.request('/')
            .its('status')
            .should('eq', 200);

        cy.visit('/login');
        cy.get('input[name="email"]').type('admin@regmel.com');
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.get('button[data-cy="submit"]').click();

        cy.contains('Empresas/OSC', { timeout: 10000 }).should('exist');
        cy.contains('Termos de Adesão', { timeout: 10000 }).click();
        cy.url().should('include', '/painel/admin/municipios-documentos');
    });

    it('Deve acessar a tela de Termos de Adesão e baixar o ZIP', () => {
        cy.get('h2').should('contain', 'Municípios - Termos de Adesão');

        cy.contains('Baixar todos os termos')
            .should('be.visible')
            .and('have.attr', 'href')
            .then((href) => {
                cy.getCookie('PHPSESSID').then((cookie) => {
                    cy.request({
                        url: href,
                        encoding: 'binary',
                        headers: {
                            Cookie: `PHPSESSID=${cookie.value}`,
                        },
                        failOnStatusCode: false,
                    }).then((response) => {
                        if (response.status === 400) {
                            cy.log('ZIP não foi gerado. Verifique se há documentos no servidor.');
                            expect(response.status).to.not.eq(400);
                        } else {
                            expect(response.status).to.eq(200);
                            expect(response.headers['content-type']).to.eq('application/zip');
                        }
                    });
                });
            });

        cy.contains('Baixar todos os termos').click({ force: true });
        cy.log('ZIP foi requisitado e resposta foi application/zip');
    });
});
