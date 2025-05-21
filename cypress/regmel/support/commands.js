// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })
import 'cypress-file-upload';

Cypress.Commands.add('loginRegmel', (email, password) => {
    cy.visit('/login');
    cy.get('[data-cy="email"]').type(email);
    cy.get('[data-cy="password"]').type(password);
    cy.get('[data-cy="submit"]').click();
});

Cypress.Commands.add('gerarCPF', (formatado = true) => {
    const numeros = Array.from({ length: 9 }, () => Math.floor(Math.random() * 10));

    function calcDigito(baseArray) {
        const pesoInicial = baseArray.length + 1;
        const soma = baseArray.reduce(
            (acc, num, idx) => acc + num * (pesoInicial - idx),
            0
        );
        const resto = soma % 11;
        return resto < 2 ? 0 : 11 - resto;
    }

    const digito1 = calcDigito(numeros);
    const digito2 = calcDigito([...numeros, digito1]);

    const cpfArray = [...numeros, digito1, digito2];
    const cpf = cpfArray.join('');

    if (formatado) {
        return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    }

    return cpf;
});

Cypress.Commands.add('gerarCNPJ', (formatado = true) => {
    const base = Array.from({ length: 8 }, () => Math.floor(Math.random() * 10));
    base.push(0, 0, 0, 1);

    function calcDigito(cnpjArray, pesos) {
        const soma = cnpjArray.reduce((acc, num, idx) => acc + num * pesos[idx], 0);
        const resto = soma % 11;
        return resto < 2 ? 0 : 11 - resto;
    }

    const peso1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    const peso2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

    const digito1 = calcDigito(base, peso1);
    const digito2 = calcDigito([...base, digito1], peso2);

    const cnpjArray = [...base, digito1, digito2];
    const cnpj = cnpjArray.join('');

    if (formatado) {
        return cnpj.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
    }
    return cnpj;
});
