describe('Convite de membro', () => {
    beforeEach(() => {
        cy.loginRegmel('usuarioNorte@empresa.com', 'Aurora@2024');
    });
    const timestamp = Date.now();
    const index = Math.floor(Math.random() * 1000);

    it(`Criador loga e envia convite`, () => {
        cy.contains('Empresas/OSC').click();
        cy.contains('Empresa Norte').click();

        cy.get('#pills-members-tab').click();
        cy.get('#pills-members')
            .should('have.class', 'show')
            .and('be.visible');

        cy.get('a[data-bs-toggle="modal"][data-bs-target="#modalInvite"]')
            .scrollIntoView()
            .should('be.visible')
            .click();

        cy.get('#modalInvite')
            .should('have.class', 'show')
            .and('be.visible');

        const membroNome = `Agente ${index}`;
        const membroEmail = `membro${index}_${timestamp}@empresa.com`;

        cy.get('input[name="name"]').should('be.visible').type(membroNome);
        cy.get('input[name="email"]')
            .focus()
            .should('be.visible')
            .clear()
            .type(membroEmail, { force: true });

        cy.get('#modalInvite button[type="submit"]')
            .should('be.visible')
            .click();

        cy.get('#modalInvite').should('not.be.visible');
    });
});
