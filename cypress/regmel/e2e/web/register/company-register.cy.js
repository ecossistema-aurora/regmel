describe('Cadastro de Empresa/OSC', () => {
    let timestamp, randomEmailEmpresa;

    beforeEach(() => {
        cy.visit('/cadastro/empresa');
    });

    it('preenche e envia o formulário com sucesso', () => {
    //     timestamp = Date.now();
    //     randomEmailEmpresa = `empresa${timestamp}@teste.com`;
    //
    //     cy.get('select#opportunity').then($select => {
    //         if ($select.find('option').length > 1) {
    //             cy.get('select#opportunity').select(1);
    //         }
    //     });
    //
    //     cy.gerarCNPJ().then((cnpj) => {
    //         cy.get('input[name="cnpj"]').type(cnpj);
    //     });
    //     cy.get('select[name="framework"]', { timeout: 10000 }).should('be.visible').select('profit');
    //     cy.get('input[name="name"]').type('Empresa Teste LTDA');
    //     cy.get('input[name="email"]').first().type(randomEmailEmpresa);
    //     cy.get('input[name="site"]').type('https://empresa.com');
    //     cy.get('input[name="phone"]').first().type('11999999999');
    //
    //     cy.get('input[name="firstname"]').type('João');
    //     cy.get('input[name="lastname"]').type('Silva');
    //     cy.get('input[name="userPhone"]').type('11988888888');
    //     cy.get('input[name="position"]').type('Diretor');
    //     cy.gerarCPF().then((cpf) => {
    //         cy.get('input[name="cpf"]').type(cpf);
    //     });
    //     cy.get('input[name="userEmail"]').type('joao@empresa.com');
    //     cy.get('input[name="password"]').type('Aurora@2024');
    //     cy.get('input[name="confirm_password"]').type('Aurora@2024');
    //
    //     cy.get('input[name="acceptTerms"]').check({ force: true });
    //     cy.get('input[name="acceptPrivacy"]').check({ force: true });
    //     cy.get('input[name="acceptImage"]').check({ force: true });
    //
    //     cy.get('button[type="submit"]').click();
    //
    //     cy.wait(5000);
    //
    //     cy.get('.toast').contains('Empresa/OSC criada com sucesso.').should('be.visible');
    //     cy.url().should('include', '/');


        cy.visit('https://localhost:32770');
    });

    it('valida se a empresa aparece na listagem após login admin', () => {
        cy.request('/').then(resp => {
            expect(resp.status).to.eq(200);
        });

        cy.visit('/');
        cy.contains('Entrar').click();

        cy.get('input[name="email"]').type('admin@regmel.com');
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.contains('button', 'Entrar').click();

        cy.contains('Empresas/OSC').click();
        cy.url().should('include', '/painel/admin/empresas');
        cy.contains('Empresa Teste LTDA').should('be.visible');
    });


    it('Acessa, preenche e envia o formulário de proposta para município não credenciado', () => {
        cy.visit('/');
        cy.contains('Entrar').click();

        cy.get('input[name="email"]').type('joao@empresa.com');
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.contains('button', 'Entrar').click();

        cy.contains('Empresas/OSC').click();
        cy.url().should('include', '/painel/admin/empresas');
        cy.contains('Empresa Teste LTDA').should('be.visible').click();

        cy.get('[data-cy="company-proposals"]').click();

        cy.get('a').contains('Nova Proposta').click();

        cy.get('select#region').select('Nordeste', {force: true});

        cy.get('#state + .ts-wrapper')
            .click()
            .find('.ts-dropdown .ts-dropdown-content')
            .contains('Ceará')
            .click();

        cy.wait(2000);

        cy.get('#city + .ts-wrapper')
            .click()
            .type('Russas')
            .find('.ts-dropdown .ts-dropdown-content')
            .contains('Russas')
            .click({force: true});

        cy.get('input[name="name"]').type('Área Teste');
        cy.get('input[name="area_characteristic"]').first().click();
        cy.get('input[name="map"]').selectFile('./cypress/regmel/fixtures/file.pdf');
        cy.get('input[name="project"]').selectFile('./cypress/regmel/fixtures/Setores_Selecionados_Novo_PAC.kml');
        cy.get('input[name="area_size"]').type('1000');
        cy.get('input[name="quantity_houses"]').type('100');

        cy.get('input[name="acceptTerms"]').check({ force: true });

        cy.get('button[type="submit"]').click();

        // Verifica se a proposta foi criada com sucesso
        cy.contains('Empresa Teste LTDA').click();
        cy.contains('Propostas').click();
        cy.get('table > tbody').contains('tr', 'Área Teste').find('[data-column-id="status"]').should('contain', 'Sem Adesão do Município');
    });

    it('Acessa, preenche e envia o formulário de proposta para município credenciado', () => {
        cy.get('a').contains('Nova Proposta').click();

        cy.get('select#region').select('Nordeste', {force: true});

        cy.get('#state + .ts-wrapper')
            .click()
            .find('.ts-dropdown .ts-dropdown-content')
            .contains('Ceará')
            .click();

        cy.wait(2000);

        cy.get('#city + .ts-wrapper')
            .click()
            .type('Santana')
            .find('.ts-dropdown .ts-dropdown-content')
            .contains('Santana do Acaraú')
            .click({force: true});

        cy.get('input[name="name"]').type('Área nas Brenhas');
        cy.get('input[name="area_characteristic"]').first().click();
        cy.get('input[name="map"]').selectFile('./cypress/regmel/fixtures/file.pdf');
        cy.get('input[name="project"]').selectFile('./cypress/regmel/fixtures/Setores_Selecionados_Novo_PAC.kml');
        cy.get('input[name="area_size"]').type('1000');
        cy.get('input[name="quantity_houses"]').type('100');

        cy.get('input[name="acceptTerms"]').check({ force: true });

        cy.get('button[type="submit"]').click();

        // Verifica se a proposta foi criada com sucesso
        cy.contains('Empresa Teste LTDA').click();
        cy.contains('Propostas').click();
        cy.get('table > tbody').contains('tr', 'Área nas Brenhas').find('[data-column-id="status"]').should('contain', 'Enviada');
    });
});
