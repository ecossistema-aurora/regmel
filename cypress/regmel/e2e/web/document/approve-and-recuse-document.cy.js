describe('Aprovar ou recusar o termo de adesão', () => {
    beforeEach(() => {
        cy.loginRegmel('admin@regmel.com', 'Aurora@2024');
    });

    it('Garante que é possivel aprovar um termo de adesão ', () => {

        cy.visit('painel/admin/municipios-documentos')
        cy.get(':nth-child(1) > [data-column-id="termoDeAdesão"] > span > a').click()

        cy.get("input[name='reason']").type('Teste de aprovação')

        cy.get("button[onclick='confirmDecision(this)']").click()

        cy.get("#confirmDecisionButton").click()

        cy.get("span[class='badge bg-success text-white text-dark text-uppercase']")


    });

    it('Garante que é possivel recusar um termo de adesão ', () => {

        cy.visit('painel/admin/municipios-documentos')
        cy.get(':nth-child(4) > [data-column-id="termoDeAdesão"] > span > a').click()

        cy.get("input[name='reason']").type('Teste de recusa')

        cy.get("button[onclick='confirmDecision(this, false)']").click()

        cy.get("#confirmDecisionButton").click()

        cy.get("span[class='badge bg-danger text-white text-dark text-uppercase']")

    });
});
