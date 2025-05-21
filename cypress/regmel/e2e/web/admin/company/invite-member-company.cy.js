const empresas = [
    { nome: 'Empresa Norte', site: 'https://empresa-norte.com' },
    { nome: 'Empresa Nordeste', site: 'https://empresa-nordeste.com' },
];

empresas.forEach((empresa, index) => {
    const timestamp = Date.now();
    const emailEmpresa = `empresa${index}_${timestamp}@teste.com`;
    const emailUsuario = `criador${index}_${timestamp}@empresa.com`;

    describe(`Fluxo convite de membro - ${empresa.nome}`, () => {

        it(`1. Cadastra a empresa ${empresa.nome}`, () => {
            cy.visit('/cadastro/empresa');

            cy.gerarCNPJ().then((cnpj) => {
                cy.get('input[name="cnpj"]').type(cnpj);
            });
            cy.get('input[name="name"]').type(empresa.nome);
            cy.get('input[name="email"]').type(emailEmpresa);
            cy.get('input[name="site"]').type(empresa.site);
            cy.get('input[name="phone"]').type('11999999999');

            cy.get('input[name="firstname"]').type('João');
            cy.get('input[name="lastname"]').type('Silva');
            cy.get('input[name="userPhone"]').type('11988888888');
            cy.get('input[name="position"]').type('Diretor');
            cy.gerarCPF().then((cpf) => {
                cy.get('input[name="cpf"]').type(cpf);
            });
            cy.get('input[name="userEmail"]').type(emailUsuario);
            cy.get('input[name="password"]').type('Aurora@2024');
            cy.get('input[name="confirm_password"]').type('Aurora@2024');

            cy.get('input[name="acceptTerms"]').check({ force: true });
            cy.get('input[name="acceptPrivacy"]').check({ force: true });
            cy.get('input[name="acceptImage"]').check({ force: true });

            cy.get('button[type="submit"]').click();
            cy.wait(2000);
        });

        it(`2. Admin confirma o usuário criador`, () => {
            cy.visit('/login');
            cy.get('input[name="email"]').type('admin@regmel.com');
            cy.get('input[name="password"]').type('Aurora@2024');
            cy.get('button[data-cy="submit"]').click();

            cy.contains('Usuários').click();
            cy.get('input[placeholder="Digite uma palavra-chave..."]').type(emailUsuario);
            cy.contains(emailUsuario).parents('tr').within(() => {
                cy.contains('Confirmar Usuário')
                    .invoke('attr', 'onclick')
                    .then(onclick => {
                        const fnBody = onclick.replace(/^.*?\(/, '').replace(/\);?$/, '');
                        const [id, name, email] = fnBody.split(',').map(s => s.trim().replace(/^\"|\"$/g, '').replace(/^'|'$/g, ''));
                        cy.window().then(win => {
                            win.modalConfirmUser(id, name, email);
                        });
                    });
            });

            cy.get('#modalConfirmUser').should('be.visible');
            cy.get('#modalConfirmUser [data-modal-button="confirm-link"]').click();
            cy.get('#modalConfirmUser').should('not.be.visible');
            cy.get('button#dropdownMenuButton').click();
            cy.get('a.dropdown-item.text-danger').should('be.visible').click();
        });

        it(`3. Criador loga e envia convite`, () => {
            cy.visit('/login');
            cy.get('input[name="email"]').type(emailUsuario);
            cy.get('input[name="password"]').type('Aurora@2024');
            cy.get('button[data-cy="submit"]').click();

            cy.contains('Empresas/OSC').click();
            cy.contains(empresa.nome).click();

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
});
