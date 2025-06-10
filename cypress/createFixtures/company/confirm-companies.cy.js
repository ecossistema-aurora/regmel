describe('Confirmação em massa de contas de empresas', () => {
    const companies = Array.from({ length: 25 }, (_, i) => `usuario.empresa${i + 1}@empresa.com`);

    before(() => {
        cy.visit('/login');
        cy.get('input[name="email"]').type('admin@regmel.com');
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.get('button[data-cy="submit"]').click();
        cy.wait(1000);
        cy.get('a').contains('Usuários').click();
    });

    it('Seleciona "Mostrar todos" e confirma as 25 contas de empresas', () => {
        const showAll = () => {
            cy.get('#gridLimitSelect')
                .select('-1')
                .should('have.value', '-1');
            cy.wait(500);
        };

        companies.forEach(email => {
            showAll();
            cy.get('table tbody')
                .contains('tr', email)
                .should('exist')
                .within(() => {
                    cy.contains('Confirmar Usuário').click();
                });

            cy.get('.modal-body').should('be.visible');
            cy.get('a[data-modal-button="confirm-link"]').click();

            showAll();
            cy.get('table tbody')
                .contains('tr', email)
                .should('exist')
                .within(() => {
                    cy.get('td').contains('Ativo').should('be.visible');
                });
        });
    });
});
