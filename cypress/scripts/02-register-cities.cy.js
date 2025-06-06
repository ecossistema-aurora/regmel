import stateCities from '../fixtures/statesCities.json';

describe('Cadastro dinâmico de Municípios', () => {
    const maxCities = 300;
    const maxPerState = 20;
    const register = [];
    let globalCounter = 0;

    Object.entries(stateCities).forEach(([stateName, citiesArray]) => {
        if (globalCounter >= maxCities) return;

        const limitCities = citiesArray.slice(0, maxPerState);
        limitCities.forEach(cityName => {
            if (globalCounter < maxCities) {
                register.push({ state: stateName, city: cityName });
                globalCounter += 1;
            }
        });
    });

    before(() => {
        cy.log(`Total de registro que serão cadastrados: ${register.length}`);
    });

    register.forEach(({ state, city }, index) => {
        it(`Cadastro ${index + 1}/${register.length}: ${city} (${state})`, () => {
            const timestamp = Date.now();
            const rawName = city
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/\s+/g, '')
                .toLowerCase();
            const randomEmail = `pref.${rawName}${timestamp}@teste.com`;
            const citySite = `https://www.${rawName}.ce.gov.br`;

            cy.visit('/cadastro/municipio');

            cy.get('input[name="firstname"]').type('Gestor');
            cy.get('input[name="lastname"]').type(city);
            cy.get('input[name="position"]').type('Prefeito(a)');
            cy.gerarCPF().then(cpfGerado => {
                cy.get('input[name="cpf"]').type(cpfGerado);
            });
            cy.get('input[name="userEmail"]').type(randomEmail);
            cy.get('input[name="password"]').type('Aurora@2024');
            cy.get('input[name="confirm_password"]').type('Aurora@2024');

            cy.get('#state + .ts-wrapper').click();
            cy.get('.ts-dropdown.single:visible')
                .should('exist')
                .find('.option')
                .contains(new RegExp(`^${state}$`, 'i'))
                .click();
            cy.wait(200);

            cy.get('#city + .ts-wrapper').click();
            cy.wait(500);
            cy.get('body').then($body => {
                if (!$body.find('.ts-dropdown.single:visible').length) {
                    cy.get('#city + .ts-wrapper').click();
                }
            });
            cy.get('.ts-dropdown.single:visible')
                .should('exist')
                .find('.option')
                .contains(new RegExp(`^${city}$`, 'i'))
                .click();

            cy.get('input[name="site"]').type(citySite);
            cy.get('input[name="phone"]').type('85912345678');
            cy.get('input[name="email"]').type(randomEmail);
            cy.get('input[name="hasHousingExperience"][value="true"]').check({ force: true });
            cy.get('input[name="hasPlhis"][value="false"]').check({ force: true });
            cy.get('input[type="file"]').selectFile('./cypress/regmel/fixtures/file.pdf');
            cy.get('input[name="acceptTerms"]').check({ force: true });
            cy.get('input[name="acceptPrivacy"]').check({ force: true });
            cy.get('input[name="acceptImage"]').check({ force: true });
            cy.get('button[type="submit"]').click();
            cy.wait(500);
            cy.get('form > :nth-child(2)').should('not.exist')
        });
    });
});
