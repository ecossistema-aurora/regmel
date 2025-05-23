describe('Editar empresa', () => {
    beforeEach(() => {
        cy.loginRegmel('usuarioNorte@empresa.com', 'Aurora@2024');
        cy.visit('/painel/admin/empresas');
    });


    it('acessa a lista de empress e garante que é possível editar uma empresa', () => {

        cy.get("button[aria-expanded='false']").click();

        cy.get("a.dropdown-item.edit-company").click();

        cy.get("#company-description")
            .should('be.visible')
            .clear()
            .type('Descrição de teste para empresa');
        
        cy.get("#tipo-entidade").click();
        
        cy.contains("Salvar").click()

        cy.get('.toast').contains('div', 'A empresa foi atualizada').should('be.visible');
    });
});
