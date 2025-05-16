describe('', () => {
    beforeEach(() => {
        cy.loginRegmel('admin@regmel.com', 'Aurora@2024');
    });

    it('Garante que é possivel aprovar um termo de adesão ', () => {

        cy.visit('painel/admin/municipios-documentos')
        cy.get("a[onclick*='PR']").click()

        cy.get("input[name='reason']").type('Teste de aprovação')

        cy.get("button[onclick='confirmDecision(this)']")

        cy.get("#confirmDecisionButton")

        cy.get("span[class='badge bg-success text-white text-dark text-uppercase']")


    });

    it('Garante que é possivel recusar um termo de adesão ', () => {

        cy.visit('painel/admin/municipios-documentos')
        cy.get("a[onclick*='SP']").click()

        cy.get("input[name='reason']").type('Teste de recusa')

        cy.get("button[onclick='confirmDecision(this)']").click()

        cy.get("#confirmDecisionButton").click()

        cy.get("span[class='badge bg-danger text-white text-dark text-uppercase']")

    });
});
