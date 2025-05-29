describe('Editar Empresa/OSC na tela de listagem', () => {

    before(() => {
        cy.loginRegmel('admin@regmel.com', 'Aurora@2024');

        cy.url().should('include', '/painel');
    });

    it('Deve editar uma empresa com sucesso', () => {
        cy.visit(`/painel/admin/empresas`);

        cy.contains('Empresa Teste LTDA')
            .closest('tr')
            .within(() => {
                cy.get('button.dropdown-toggle').click();
            });

        cy.get('.dropdown-menu.show').contains('Editar').click();

        cy.get('#modalEditCompany').should('be.visible');

        const novaDescricao = 'Editado via Cypress em produção';
        cy.get('#company-description').clear().type(novaDescricao);

        cy.get('button[form="edit-company"]').click();

        cy.get('#modalEditCompany').should('not.be.visible');

    });
});
