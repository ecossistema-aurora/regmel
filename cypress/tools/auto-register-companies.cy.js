describe('Cadastro automático de 50 empresas e entidades', () => {
    const empresas = Array.from({ length: 50 }, (_, i) => ({
        nome: i < 25 ? `Empresa${i + 1}` : `Entidade${i - 24}`,
        site: i < 25 ? `https://empresa${i + 1}.com` : `https://entidade${i - 24}.com`,
        user: i < 25 ? `usuario.empresa${i + 1}` : `usuario.entidade${i - 24}`,
        tipo: i < 25 ? 'empresa' : 'entidade'
    }));

    empresas.forEach((empresa, index) => {
        it(`Cadastra a ${empresa.nome} (${empresa.tipo})`, () => {
            const randomEmail = `${empresa.user}@${empresa.tipo}.com`;

            cy.visit('/cadastro/empresa');
            cy.gerarCNPJ().then((cnpj) => {
                cy.get('input[name="cnpj"]').type(cnpj);
            });
            cy.get('input[name="name"]').type(`${empresa.nome} Teste`);
            cy.get('input[name="email"]').type(randomEmail);
            cy.get('input[name="site"]').type(empresa.site);
            cy.get('input[name="phone"]').type('11999999999');

            cy.get('input[name="firstname"]').type(empresa.tipo.charAt(0).toUpperCase() + empresa.tipo.slice(1));
            cy.get('input[name="lastname"]').type(empresa.nome);
            cy.get('input[name="userPhone"]').type('11988888888');
            cy.get('input[name="position"]').type('Diretor');
            cy.gerarCPF().then((cpf) => {
                cy.get('input[name="cpf"]').type(cpf);
            });
            cy.get('input[name="userEmail"]').type(randomEmail);
            cy.get('input[name="password"]').type('Aurora@2024');
            cy.get('input[name="confirm_password"]').type('Aurora@2024');

            cy.get('input[name="acceptTerms"]').check({ force: true });
            cy.get('input[name="acceptPrivacy"]').check({ force: true });
            cy.get('input[name="acceptImage"]').check({ force: true });

            cy.get('button[type="submit"]').click();
            cy.wait(3000);
        });
    });
});