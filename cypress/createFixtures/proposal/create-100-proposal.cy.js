describe('Cadastro em massa de 100 propostas', () => {
    const companies = Array.from({ length: 25 }, (_, i) => ({
        email: `usuario.empresa${i + 1}@empresa.com`,
        password: 'Aurora@2024'
    }));

    const places = {
        'Ceará': ['Fortaleza', 'Abaiara', 'Acarape', 'Acaraú' ],
        'Rio de Janeiro': ['Rio de Janeiro', 'Angra dos Reis', 'Aperibé', 'Araruama' ],
        'Minas Gerais': ['Belo Horizonte', 'Uberlândia', 'Ouro Preto', 'Poços de Caldas' ],
        'São Paulo': ['São Paulo', 'Campinas', 'Santos', 'Sorocaba' ],
        'Bahia': ['Salvador', 'Feira de Santana', 'Itabuna', 'Ilhéus' ],
        'Santa Catarina': [ 'Florianópolis', 'Abdon Batista', 'Abelardo Luz', 'Agrolândia'],
        'Sergipe': ['Aracaju', 'Amparo do São Francisco', 'Aquidabã', 'Arauá'],
        'Paraná': ['Curitiba', 'Londrina', 'Maringá', 'Ponta Grossa' ],
        'Rio Grande do Sul': ['Porto Alegre', 'Caxias do Sul', 'Pelotas', 'Santa Maria' ],
        'Pernambuco': ['Recife', 'Olinda', 'Caruaru', 'Petrolina' ],
        'Amazonas': ['Manaus', 'Parintins', 'Tabatinga', 'Tefé' ],
        'Pará': ['Belém', 'Ananindeua', 'Marabá', 'Santarém' ],
        'Goiás': ['Goiânia', 'Aparecida de Goiânia', 'Anápolis', 'Rio Verde' ],
        'Espírito Santo': ['Vitória', 'Vila Velha', 'Cariacica', 'Serra' ],
        'Mato Grosso': ['Cuiabá', 'Várzea Grande', 'Rondonópolis', 'Sinop' ],
        'Mato Grosso do Sul': ['Campo Grande', 'Dourados', 'Três Lagoas', 'Corumbá' ],
        'Alagoas': ['Maceió', 'Arapiraca', 'Palmeira dos Índios', 'Rio Largo' ],
        'Paraíba': ['João Pessoa', 'Campina Grande', 'Santa Rita', 'Patos' ],
        'Rio Grande do Norte': ['Natal', 'Mossoró', 'Parnamirim', 'Caicó' ],
        'Tocantins': ['Palmas', 'Araguaína', 'Gurupi', 'Paraíso do Tocantins' ],
        'Rondônia': ['Porto Velho', 'Ji-Paraná', 'Ariquemes', 'Vilhena' ],
        'Acre': ['Rio Branco', 'Cruzeiro do Sul', 'Sena Madureira', 'Tarauacá' ],
        'Piauí': ['Teresina', 'Parnaíba', 'Picos', 'Floriano' ],
        'Roraima': ['Boa Vista', 'Rorainópolis', 'Caracaraí', 'Mucajaí' ],
        'Amapá': ['Macapá', 'Santana', 'Laranjal do Jari', 'Mazagão' ],
        'Maranhão': ['São Luís', 'Imperatriz', 'Caxias', 'Timon' ],
    };

    const placesList = [];
    for (const [state, cities] of Object.entries(places)) {
        for (const city of cities) {
            placesList.push({ state, city });
            if (placesList.length >= 100) break;
        }
        if (placesList.length >= 100) break;
    }

    it('Cadastra 100 propostas (4 por empresa)', () => {
        let proposalIndex = 0;

        companies.forEach(({ email, password }) => {
            cy.visit('/login');
            cy.get('input[name="email"]').type(email);
            cy.get('input[name="password"]').type(password);
            cy.get('button[data-cy="submit"]').click();
            cy.wait(1000);
            cy.visit('/painel/admin/empresas');
            cy.contains('Propostas').click();

            for (let i = 0; i < 4; i++, proposalIndex++) {
                const { state, city } = placesList[proposalIndex];
                cy.contains('Propostas').click();
                cy.contains('Nova Proposta').click();
                cy.get('#state + .ts-wrapper')
                    .click()
                    .find('.ts-dropdown .ts-dropdown-content')
                    .contains(state)
                    .click();
                cy.wait(1000);

                cy.get('#city + .ts-wrapper')
                    .click()
                    .type(city)
                    .find('.ts-dropdown .ts-dropdown-content')
                    .contains(city)
                    .click({ force: true });

                cy.get('input[name="name"]').type(`Área Teste ${proposalIndex + 1}`);
                cy.get('input[name="area_characteristic"]').first().click();
                cy.get('input[name="map"]')
                    .selectFile('./cypress/regmel/fixtures/file.pdf');
                cy.get('input[name="project"]')
                    .selectFile('./cypress/regmel/fixtures/Setores_Selecionados_Novo_PAC.kml');
                cy.get('input[name="area_size"]').type('1000');
                cy.get('input[name="quantity_houses"]').type('100');
                cy.get('input[name="acceptTerms"]').check({ force: true });
                cy.get('button[type="submit"]').click();
            }

            cy.clearCookies();
            cy.clearLocalStorage();
        });

        expect(proposalIndex).to.equal(100);
    });
});
