describe('Listagem de Usuários - Painel Administrativo', () => {
    beforeEach(() => {
        cy.loginRegmel('admin@regmel.com', 'Aurora@2024');
        cy.visit('/painel/admin/usuarios');
    });

    it('Carrega a tela e exibe os usuários', () => {
        cy.get('table').should('be.visible');
        cy.get('table thead').within(() => {
            cy.contains('Nome');
            cy.contains('Email');
            cy.contains('Imagem');
            cy.contains('Status');
            cy.contains('Criado em');
            cy.contains('Perfis');
            cy.contains('Ações');
        });
        cy.get('table tbody tr').should('have.length.greaterThan', 0);
    });

    it('Filtra usuários pelo campo de busca', () => {
        cy.get('.gridjs-input').type('João');
        cy.wait(1000);
        cy.get('table tbody tr').each(($tr) => {
            cy.wrap($tr).should('contain.text', 'João');
        });
        cy.get('.gridjs-input').clear().type('admin@regmel.com');
        cy.get('table tbody tr').each(($tr) => {
            cy.wrap($tr).should('contain.text', 'admin@regmel.com');
        });
    });

    it('Botão "Confirmar Usuário" aparece apenas para status pendente', () => {
        cy.get('table tbody tr').each(($tr) => {
            cy.wrap($tr).within(() => {
                cy.get('td[data-column-id="status"]').then($status => {
                    if ($status.text().includes('Aguardando Confirmar Conta')) {
                        cy.contains('Confirmar Usuário').should('exist');
                    } else {
                        cy.contains('Confirmar Usuário').should('not.exist');
                    }
                });
            });
        });
    });

    it('Confirma usuário pendente e atualiza status', () => {
        cy.get('table tbody tr').contains('Aguardando Confirmar Conta').parents('tr').within(() => {
            cy.contains('Confirmar Usuário').click();
        });
        cy.get('.modal-body').should('be.visible');
        cy.get('a[data-modal-button="confirm-link"]').click();
        cy.get('table tbody tr').contains('Ativo').should('exist');
    });

    it('Valida paginação e navegação entre páginas', () => {
        cy.get('#gridLimitSelect').select('10');

        cy.get('button').contains('Próximo').then($next => {
            if (!$next.is(':disabled')) {
                cy.wrap($next).click();
                cy.get('table tbody tr').should('have.length.greaterThan', 0);

                cy.get('button').contains('Anterior').then($prev => {
                    if (!$prev.is(':disabled')) {
                        cy.wrap($prev).click();
                        cy.get('table tbody tr').should('have.length.greaterThan', 0);
                    } else {
                        cy.log('Botão "Anterior" está desabilitado, provavelmente na primeira página.');
                    }
                });
            } else {
                cy.log('Botão "Próximo" está desabilitado, sem próximas páginas.');
            }
        });
    });

    it('Garante atualização dos dados ao trocar de página', () => {
        cy.get('#gridLimitSelect').select('10');
        let firstPageFirstUser;
        cy.get('table tbody tr').first().find('td').first().invoke('text').then(text => {
            firstPageFirstUser = text;
        });
        cy.get('button').contains('Próximo').click();
        cy.get('table tbody tr').first().find('td').first().invoke('text').should('not.eq', firstPageFirstUser);
    });
});
