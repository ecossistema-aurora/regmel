describe('Extrai lista de estados e cidades via API e grava em fixture', () => {
    it('Deve obter todos os estados e suas cidades e salvar em cypress/fixtures/statesCities.json', () => {
        cy.request({
            method: 'GET',
            url: '/api/states',
            failOnStatusCode: true
        }).then(responseStates => {
            expect(responseStates.status).to.equal(200);
            const listStates = responseStates.body;
            const statesCities = {}
            
            cy.wrap(listStates).each(estadoObj => {
                const name = estadoObj.name;
                const stateId = estadoObj.id;

                cy.request({
                    method: 'GET',
                    url: `/api/states/${stateId}/cities`,
                    failOnStatusCode: true
                }).then(responseCities => {
                    expect(responseCities.status).to.equal(200);
                    const citiesName = responseCities.body.map(ci => ci.name);
                    statesCities[name] = citiesName;
                });
            })
                .then(() => {
                    cy.writeFile('cypress/fixtures/statesCities.json', statesCities, { spaces: 2 });
                    cy.log('Fixture "statesCities.json" criada com sucesso');
                });
        });
    });
});
