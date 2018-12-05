import cards from '../fixtures/credit-cards.json'

Cypress.Commands.add("payCcHosted", (method) => {

    cy.location('pathname', {timeout: 50000}).should('include', '/web/pay');

    cy.get('#cardNumber').type(cards[method].number);
    cy.get('#cardExpiryMonth').select(cards[method].expiryMonth);
    cy.get('#cardExpiryYear').select(cards[method].expiryYear);
    cy.get('#cardSecurityCode').type(cards[method].cvv);

    cy.get('#submit-button').click();
});

Cypress.Commands.add("payCcIframe", ($iframe, method) => {

    const $body = $iframe.contents().find('body');

    cy.wrap($body).find('#cardNumber').type(cards[method].number);
    cy.wrap($body).find('#cardExpiryMonth').select(cards[method].expiryMonth);
    cy.wrap($body).find('#cardExpiryYear').select(cards[method].expiryYear);
    cy.wrap($body).find('#cardSecurityCode').type(cards[method].cvv);
    cy.wrap($body).find('#submit-button').click();
});
