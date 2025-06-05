describe('Teste de reenvio de termo de adesão', () => {
    beforeEach(() => {
        cy.loginRegmel('jose1@municipio.com', 'Aurora@2024');
        cy.visit('/painel')
    });

    it('Garante que é possível reenviar o termo de adesão do município', () => {
        cy.wait(500);

        cy.contains('Santana do Acaraú-CE');
        cy.get('#pills-files-tab').contains('Termo de Adesão').click();
        cy.get('.alert-danger').contains('RECUSADO').should('be.visible');
        cy.wait(1000)
        cy.get('#pills-files').should('contain.text', 'Termo-SantanaDoAcarau-CE-1.pdf');
        cy.get('.d-flex > .btn-outline-success').contains('Reenviar').click();
        cy.get('#joinForm').selectFile('./cypress/regmel/fixtures/file2.pdf');
        cy.get('#modalResendTerm > .modal-dialog > .modal-content > form > .modal-footer > .btn-primary').contains('Enviar').click();
        cy.get('.toast').contains('Termo enviado com sucesso!').should('be.visible');
        cy.get('span > a').contains('Santana do Acaraú-CE').click();
        cy.get('#pills-files-tab').contains('Termo de Adesão').click();
        cy.get('div.alert-warning').contains('AGUARDANDO').should('be.visible');
        cy.get('#pills-files').should('contain.text', 'Termo-SantanaDoAcarau-CE-2.pdf');
    });
})