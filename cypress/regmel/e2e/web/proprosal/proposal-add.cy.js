describe('Cadastro de proposta', () => {
    beforeEach(() => {
        cy.visit('/login');
        cy.get('input[name="email"]').type('joao@empresa.com');
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.get('button[data-cy="submit"]').click();
        cy.wait(1000);
        cy.visit('/painel/admin/empresas');
        cy.contains('Propostas').click();
    });

    it('Acessa, preenche e envia o formulário de proposta para município não credenciado', () => {
        cy.get('a').contains('Nova Proposta').click();


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

describe('Cadastro de proposta com empresa do tipo entidade', () => {
    beforeEach(() => {
        cy.loginRegmel('usuarioNorte@empresa.com', 'Aurora@2024');
        cy.wait(1000);
        cy.visit('/painel/admin/empresas');
        cy.contains('Propostas').click();
    });

    it('Acessa uma empresa do tipo entidade, preenche e envia o formulário de proposta com antecipação de recurso para município não credenciado', () => {
        cy.get('a').contains('Nova Proposta').click();


        cy.get('#state + .ts-wrapper')
            .click()
            .find('.ts-dropdown .ts-dropdown-content')
            .contains('Acre')
            .click();

        cy.wait(2000);

        cy.get('#city + .ts-wrapper')
            .click()
            .type('Bujari')
            .find('.ts-dropdown .ts-dropdown-content')
            .contains('Bujari')
            .click({force: true});

        cy.wait(1000);

        cy.get('input[name="name"]').type('Área Teste');
        cy.get('input[name="area_characteristic"]').first().click();
        cy.get('input[name="map"]').selectFile('./cypress/regmel/fixtures/file.pdf');
        cy.get('input[name="project"]').selectFile('./cypress/regmel/fixtures/Setores_Selecionados_Novo_PAC.kml');
        cy.get('input[name="area_size"]').type('1000');
        cy.get('input[name="quantity_houses"]').type('100');

        cy.get('input[name="acceptTerms"]').check({ force: true });

        cy.get("#antecipacao_sim").click()
        cy.get("input[data-label$='Anexo IV-C']").selectFile('./cypress/regmel/fixtures/file.pdf');
        cy.get("input[data-label='Responsável técnico']").selectFile('./cypress/regmel/fixtures/file.pdf');
        cy.get("input[data-label$='ART']").selectFile('./cypress/regmel/fixtures/file.pdf');


        cy.get('button[type="submit"]').click();

        cy.contains('Empresa Norte').click();
        cy.contains('Propostas').click();
        cy.get('table > tbody').contains('tr', 'Área Teste').find('[data-column-id="status"]').should('contain', 'Sem Adesão do Município');
    });

    it('Acessa uma empresa do tipo entidade, preenche e envia o formulário de proposta com antecipação de recurso para município credenciado', () => {
        cy.get('a').contains('Nova Proposta').click();


        cy.get('#state + .ts-wrapper')
            .click()
            .find('.ts-dropdown .ts-dropdown-content')
            .contains('Amazonas')
            .click();

        cy.wait(2000);

        cy.get('#city + .ts-wrapper')
            .click()
            .type('Manaus')
            .find('.ts-dropdown .ts-dropdown-content')
            .contains('Manaus')
            .click({force: true});

        cy.wait(1000);
        cy.get('input[name="name"]').type('Área Teste 2');
        cy.get('input[name="area_characteristic"]').first().click();
        cy.get('input[name="map"]').selectFile('./cypress/regmel/fixtures/file.pdf');
        cy.get('input[name="project"]').selectFile('./cypress/regmel/fixtures/Setores_Selecionados_Novo_PAC.kml');
        cy.get('input[name="area_size"]').type('1000');
        cy.get('input[name="quantity_houses"]').type('100');

        cy.get('input[name="acceptTerms"]').check({ force: true });

        cy.get("#antecipacao_sim").click()
        cy.get("input[data-label$='Anexo IV-C']").selectFile('./cypress/regmel/fixtures/file.pdf');
        cy.get("input[data-label='Responsável técnico']").selectFile('./cypress/regmel/fixtures/file.pdf');
        cy.get("input[data-label$='ART']").selectFile('./cypress/regmel/fixtures/file.pdf');


        cy.get('button[type="submit"]').click();

        cy.contains('Empresa Norte').click();
        cy.contains('Propostas').click();
        cy.get('table > tbody').contains('tr', 'Área Teste 2').find('[data-column-id="status"]').should('contain', 'Enviada');
    });
});
