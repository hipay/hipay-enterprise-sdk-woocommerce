import hipay from '../fixtures/hipay-bo.json'

Cypress.Commands.add("HiPayBOConnect", () => {
    cy.visit('https://stage-merchant.hipay-tpp.com');
    cy.get('#email-address').type(hipay.login);
    cy.get('#password').type(hipay.password);
    cy.get('#submit-login').click();
});

Cypress.Commands.add("HiPayBOSelectAccount", () => {
    cy.get('.btn-group > .dropdown-toggle').click();
    cy.get('.business').contains(hipay.business).find('i').click();
    cy.get('.account-name').contains(hipay.account).parent().find('a[title="Switch to this account"]').click();
});

Cypress.Commands.add("HiPayBOGoToTransaction", (id) => {
    cy.get('.nav-transactions').click();
    cy.get('#submitbutton').click();
    cy.get('.datatable-transactions td:nth-child(4)')
        .contains(id)
        .parent()
        .parent()
        .find('td > a[data-original-title="View transaction details"]')
        .click();
});

Cypress.Commands.add("HiPayBOOpenNotifications", (status) => {

    let hash;
    let data;

    cy.get('.nav > :nth-child(5) > a').click();
    cy.get('#payment-notification-container > .table > tbody td:nth-child(4)')
        .contains(status).parent().parent().find(".details-notification").click();

    cy.get("#copy-transaction-message").then(($data) => {
        data = $data.text();
        cy.wrap(data).as("data");
    });

    cy.get(".list-fraud-review").contains("Hash").then(($data) => {
        hash = $data.text().match(/Hash: (.*)/m)[1];
        cy.wrap(hash).as("hash");
    });
});
