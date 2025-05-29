describe('Editar Empresa/OSC na tela de listagem', () => {
    const novaDescricao = 'Editado via Cypress em produção';

    before(() => {
        cy.loginRegmel('admin@regmel.com', 'Aurora@2024');

        cy.visit('/painel');
        cy.contains('Empresas/OSC').click();
        cy.url().should('include', '/painel/admin/empresas');
    });

    it('Deve editar uma empresa com sucesso e validar na timeline', () => {
        cy.contains('Empresa Teste LTDA')
            .closest('tr')
            .within(() => {
                cy.get('td').eq(2).invoke('text').as('descricaoAnterior');
                cy.get('button.dropdown-toggle').click();
            });

        cy.get('.dropdown-menu.show').contains('Editar').click();
        cy.get('#modalEditCompany').should('be.visible');

        cy.get('#company-description').clear();
        cy.wait(2000);
        cy.get('#company-description').focus().type(novaDescricao);

        cy.get('button[form="edit-company"]').click();
        cy.get('#modalEditCompany').should('not.be.visible');

        cy.get('div.toast-body')
            .should('be.visible')
            .and('contain', 'A empresa foi atualizada');

        cy.contains('Empresa Teste LTDA')
            .closest('tr')
            .within(() => {
                cy.get('td').eq(2).should('have.text', novaDescricao);
            });

        cy.contains('Empresa Teste LTDA')
            .closest('tr')
            .within(() => {
                cy.get('td').eq(2).should('contain', 'Editado');
            });

        cy.contains('Empresa Teste LTDA').click();
        cy.url().should('include', '/painel/admin/empresas/');

        cy.contains('Linha do tempo').click();

        cy.get('table')
            .contains('A entidade foi atualizada')
            .first()
            .parents('tr')
            .within(() => {
                cy.contains('Detalhes').click();
            });

        cy.get('#modal-timeline-table-body')
            .contains('td', 'Campos extras')
            .parent()
            .within(() => {
                cy.get('td').eq(2).invoke('text');
            });
    });
});
