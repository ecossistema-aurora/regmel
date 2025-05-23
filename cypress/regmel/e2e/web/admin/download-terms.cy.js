describe('Fluxo de Exportação de Termos de Adesão como ZIP', () => {
    beforeEach(() => {
        cy.request('/')
            .its('status')
            .should('eq', 200);

        cy.visit('/login');
        cy.get('input[name="email"]').type('admin@regmel.com');
        cy.get('input[name="password"]').type('Aurora@2024');
        cy.get('button[data-cy="submit"]').click();

        cy.contains('Empresas/OSC', { timeout: 10000 }).should('exist');
        cy.contains('Termos de Adesão', { timeout: 10000 }).click();
        cy.url().should('include', '/painel/admin/municipios-documentos');
    });

    it('Deve acessar a tela de Termos de Adesão e baixar o ZIP', () => {
        cy.get('h2').should('contain', 'Municípios - Termos de Adesão');

        cy.contains('Baixar todos os termos')
            .should('be.visible')
            .and('have.attr', 'href')
            .then((href) => {
                cy.getCookie('PHPSESSID').then((cookie) => {
                    cy.request({
                        url: href,
                        encoding: 'binary',
                        headers: {
                            Cookie: `PHPSESSID=${cookie.value}`,
                        },
                        failOnStatusCode: false,
                    }).then((response) => {
                        if (response.status === 400) {
                            cy.log('ZIP não foi gerado. Verifique se há documentos no servidor.');
                            expect(response.status).to.not.eq(400);
                        } else {
                            expect(response.status).to.eq(200);
                            expect(response.headers['content-type']).to.eq('application/zip');
                        }
                    });
                });
            });

        cy.contains('Baixar todos os termos').click({ force: true });
        cy.log('ZIP foi requisitado e resposta foi application/zip');
    });
});
