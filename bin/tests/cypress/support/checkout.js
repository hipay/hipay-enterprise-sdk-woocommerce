Cypress.Commands.add("goToFront", () => {
    cy.visit('/');
});

Cypress.Commands.add("selectItemAndGoToCart", () => {
    cy.goToFront();
    cy.get('.post-73 > .button').click();
    cy.get('.added_to_cart').click();
});

Cypress.Commands.add("addProductQuantity", (qty) => {
    cy.get('.qty').clear();
    cy.get('.qty').type(qty);
    cy.get('[name="update_cart"]').click();
    cy.get('.woocommerce-message')
});

Cypress.Commands.add("goToCheckout", () => {
    cy.get('.checkout-button').click();
});

Cypress.Commands.add("fillBillingForm", () => {
    cy.fixture("customerFR").then((customer) => {
        console.log(customer);
        cy.get('#billing_first_name').type(customer.firstName);
        cy.get('#billing_last_name').type(customer.lastName);
        cy.get('#billing_country').select(customer.country, {force: true});
        cy.get('#billing_address_1').type(customer.streetAddress);
        cy.get('#billing_postcode').type(customer.zipCode);
        cy.get('#billing_city').type(customer.city);
        cy.get('#billing_phone').type(customer.phone);
        cy.get('#billing_email').type(customer.email);
    });
});

Cypress.Commands.add("checkOrderSuccess", () => {
    cy.location('pathname', {timeout: 50000}).should('include', '/checkout/order-received');
});

Cypress.Commands.add("checkOrderCancelled", () => {
    cy.location('pathname', {timeout: 50000}).should('include', '/cart/');
    cy.get('.woocommerce-info').contains("Your order was cancelled");
});

Cypress.Commands.add("saveLastOrderId", () => {
    cy.get('body').then(($body) => {
        if ($body.find('.woocommerce-order-overview__order > strong').length) {

            cy.get('.woocommerce-order-overview__order > strong').then(($data) => {

                cy.fixture('order').then((order) => {
                    order.lastOrderId = $data.text();
                    cy.writeFile('cypress/fixtures/order.json', order);
                });
            });
        }
    });
});

Cypress.Commands.add("saveLastCapturedOrderId", () => {
    cy.fixture('order').then((order) => {
        order.lastCapturedOrderId = order.lastOrderId;
        cy.writeFile('cypress/fixtures/order.json', order);
    });
});
