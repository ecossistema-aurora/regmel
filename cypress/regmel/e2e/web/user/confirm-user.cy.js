describe('Confirmação do usuário', () => {
    beforeEach(() => {
        cy.visit('/login');
        cy.get('input[name="email"]').type('admin@regmel.com');
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.get('button[data-cy="submit"]').click();
        cy.wait(1000);
    });

    it('Acessa e confirma usuário não validado', () => {
        cy.get('a').contains('Usuários').click();

        cy.get('table tbody tr').contains('Aguardando Confirmar Conta').parents('tr').within(() => {
            cy.contains('Confirmar Usuário').click();
        });
        cy.get('.modal-body').should('be.visible');
        cy.get('a[data-modal-button="confirm-link"]').click();
        cy.get('table tbody tr').contains('Ativo').should('exist');

        cy.contains('table tbody tr', 'jose1@municipio.com').within(() => {
            cy.contains('Confirmar Usuário').click();
        });
        cy.get('.modal-body').should('be.visible');
        cy.get('a[data-modal-button="confirm-link"]').click();
        cy.get('table tbody tr').contains('Ativo').should('exist');
    });

    it('Acessa e confirma usuário especifico não validado', () => {
        cy.get('a').contains('Usuários').click();

        cy.get('.gridjs-input').type('Empresa Norte');

        cy.get('table tbody tr').contains('Empresa Norte').parents('tr').within(() => {
            cy.contains('Confirmar Usuário').click();
        });
        cy.get('.modal-body').should('be.visible');
        cy.get('a[data-modal-button="confirm-link"]').click();

        cy.get('table tbody tr').contains('Empresa Norte').parents('tr').contains('Ativo').should('exist');

    });
});
