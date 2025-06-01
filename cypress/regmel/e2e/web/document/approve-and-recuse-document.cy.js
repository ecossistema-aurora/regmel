describe('Teste de aprovação de termo de adesão', () => {
    beforeEach(() => {
        cy.loginRegmel('admin@regmel.com', 'Aurora@2024');
    });

    it('Garante que é possível recusar um termo de adesão', () => {
        cy.visit('painel/admin/municipios-documentos');

        cy.contains('tbody tr', 'Santana do Acaraú - CE').within(() => {
            cy.get('td[data-column-id="status"] span')
                .invoke('text')
                .then((rawText) => {
                    expect(rawText.trim()).to.contain('Aguardando');
                });

            cy.get('[data-column-id="termoDeAdesão"] a').click();
        });

        cy.get('#documentDecisionForm').should('be.visible');

        cy.get("input[name='reason']")
            .type('Recusa automática');

        cy.get('#documentDecisionForm')
            .contains('button', 'Recusar')
            .click();

        cy.get('#confirmDecisionButton').click();

        cy.get('#modalDocument').should('not.be.visible');

        cy.contains('td', 'Santana do Acaraú - CE')
            .parent('tr')
            .find('td[data-column-id="status"] span')
            .invoke('text')
            .then((rawText) => {
                expect(rawText.trim()).to.contain('Rejeitado');
            })  ;
    });

    it('Garante que é possível aprovar um termo de adesão disponível', () => {
        cy.visit('painel/admin/municipios-documentos');

        cy.contains('td', 'Aguardando')
            .parent('tr')
            .as('linhaAguardando');

        cy.get('@linhaAguardando')
            .find('td')
            .first()
            .invoke('text')
            .then((rawText) => {
                const city = rawText.trim();

                cy.get('@linhaAguardando')
                    .find('[data-column-id="termoDeAdesão"] a')
                    .click();

                cy.get('#documentDecisionForm').should('be.visible');

                cy.get("input[name='reason']")
                    .type(`Aprovação automática - ${city}`);
                cy.get("button[onclick='confirmDecision(this)']").click();

                cy.get('#confirmDecisionButton').click();

                cy.get('#modalDocument').should('not.be.visible');

                cy.contains('td', city)
                    .parent('tr')
                    .as('linhaAtualizada');

                cy.get('@linhaAtualizada')
                    .find('td[data-column-id="status"] span')
                    .invoke('text')
                    .then((rawTextStatus) => {
                        expect(rawTextStatus.trim()).to.contain('Aceito');
                    });
            });
    });
});
