describe('Teste de aprovação de termo de adesão', () => {
    beforeEach(() => {
        cy.loginRegmel('admin@regmel.com', 'Aurora@2024');
    });

    it('Garante que é possível aprovar um termo de adesão disponível', () => {
        cy.visit('painel/admin/municipios-documentos');

        cy.get('tbody tr').each(($row) => {
            const statusText = $row.find('td[data-column-id="status"] span').text().trim();
            const cidade = $row.find('td').first().text().trim();

            if (statusText === 'AGUARDANDO') {
                cy.wrap($row).within(() => {
                    cy.get('[data-column-id="termoDeAdesão"] a').click();
                });

                cy.get('#documentDecisionForm').should('be.visible');

                cy.get("input[name='reason']").type(`Aprovação automática - ${cidade}`);
                cy.get("button[onclick='confirmDecision(this)']").click();
                cy.get("#confirmDecisionButton").click();

                cy.get('#modalDocument').should('not.be.visible');

                cy.contains('td', cidade)
                    .parent('tr')
                    .find('td[data-column-id="status"] span')
                    .should('contain.text', 'ACEITO')
                    .and('have.class', 'bg-success');

                return false;
            }
        });
    });
});
