describe('Cadastro de Município', () => {
    let timestamp, randomEmailMunicipio, randomEmailUsuario, randomCPF;

    beforeEach(() => {
        timestamp = Date.now();
        randomEmailMunicipio = `municipio${timestamp}@teste.com`;
        randomEmailUsuario = `joao${timestamp}@municipio.com`;
        randomCPF = `${Math.floor(10000000000 + Math.random() * 89999999999)}`;
        cy.visit('/cadastro/municipio');
    });

    it('preenche e envia o formulário com sucesso', () => {
        cy.get('select#opportunity').then($select => {
            if ($select.find('option').length > 1) {
                cy.get('select#opportunity').select(1);
            }
        });

        cy.get('input[name="firstname"]').type('João');
        cy.get('input[name="lastname"]').type('Silva');
        cy.get('input[name="position"]').type('Diretor');
        cy.get('input[name="cpf"]').type(randomCPF);
        cy.get('input[name="userEmail"]').type(randomEmailUsuario);
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.get('input[name="confirm_password"]').type('Aurora@2024');

        cy.get('select#state').select('Ceará');
        cy.get('select#city', { timeout: 5000 }).select('Santana do Acaraú');

        cy.get('input[name="site"]').type('https://municipio.ce.gov.br');
        cy.get('input[name="phone"]').first().type('11999999999');
        cy.get('input[name="email"]').first().type(randomEmailMunicipio);
        cy.get('input[type=file]').selectFile('./cypress/regmel/fixtures/file.pdf')

        cy.get('input[name="acceptTerms"]').check({ force: true });
        cy.get('input[name="acceptPrivacy"]').check({ force: true });
        cy.get('input[name="acceptImage"]').check({ force: true });

        cy.get('button[type="submit"]').click();

        cy.wait(5000); // Espera bruta para possível redirect ou render
    });

    it('valida se o município aparece na listagem após login admin', () => {
        cy.request('/').then(resp => {
            expect(resp.status).to.eq(200);
        });

        cy.visit('/');
        cy.contains('Entrar').click();

        cy.get('input[name="email"]').type('admin@regmel.com');
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.contains('button', 'Entrar').click();

        cy.wait(5000);

        cy.contains('Municípios').click();
        cy.url().should('include', '/painel/admin/municipios');
        cy.contains('Santana do Acaraú').should('be.visible');
    });

    it('valida se o documento aparece na listagem com o nome correto', () => {
        cy.request('/').then(resp => {
            expect(resp.status).to.eq(200);
        });

        cy.visit('/');
        cy.contains('Entrar').click();

        cy.get('input[name="email"]').type('admin@regmel.com');
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.contains('button', 'Entrar').click();

        cy.wait(5000);

        cy.contains('Documentos').click();
        cy.url().should('include', '/painel/admin/municipios-documentos');
        cy.contains('Termo-SantanaDoAcarau-CE-1.pdf').should('be.visible');
    });
});
