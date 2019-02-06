/**
 * GO to Home
 */
Cypress.Commands.add("goToFront", () => {
    cy.visit('/');
});

/**
 * Select just an item (Album) and add it to the cart
 */
Cypress.Commands.add("selectItemAndGoToCart", () => {
    cy.goToFront();
    cy.get('.post-73 > .add_to_cart_button').click();
    cy.get('.added_to_cart', {timeout: 50000}).click();
});

/**
 * Select several product and add them to the cart
 */
Cypress.Commands.add("selectSeveralItemsAndGoToCart", () => {
    cy.goToFront();
    cy.get('.post-73 .add_to_cart_button').click();
    cy.get('.post-73 .added_to_cart', {timeout: 50000});
    cy.get('.post-48 .add_to_cart_button').click();
    cy.get('.post-48 .added_to_cart', {timeout: 50000});
    cy.get('.post-85 .add_to_cart_button').click();
    cy.get('.post-85 .added_to_cart', {timeout: 50000});
    cy.get('.post-85 .added_to_cart').click();
});

/**
 * Add coupon code test and apply it for the cart
 */
Cypress.Commands.add("addAndApplyCouponCode", () => {
    cy.get('#coupon_code').type("test");
    cy.get('.coupon > .button').click();
});

/**
 * Proceed checkout and pay with card
 */
Cypress.Commands.add("proceedToCheckout", (card) => {
    cy.goToCheckout();
    cy.fillBillingForm();
    cy.get('.payment_method_hipayenterprise_credit_card > label').click({force: true});
    cy.get('#hipay-field-cardHolder > iframe');
    cy.wait(3000);
    cy.fill_hostedfield_card(card);
    cy.get('#place_order').click({force: true});
});


/**
 * Set different quantity for all items
 */
Cypress.Commands.add("addProductQuantityForSeveralItems", (qty) => {
    cy.get('table.shop_table tr:nth-child(1) .qty').clear();
    cy.get('table.shop_table tr:nth-child(1) .qty').type(qty);
    cy.get('table.shop_table tr:nth-child(2) .qty').clear();
    cy.get('table.shop_table tr:nth-child(2) .qty').type(qty + 3);
    cy.get('table.shop_table tr:nth-child(3) .qty').clear();
    cy.get('table.shop_table tr:nth-child(3) .qty').type(qty + 5);
    cy.get('[name="update_cart"]').click();
    cy.get('.woocommerce-message', {timeout: 50000})
});

/**
 * Adjust QTY for an product
 */
Cypress.Commands.add("addProductQuantity", (qty) => {
    cy.get('.qty').first().clear();
    cy.get('.qty').first().type(qty);
    cy.get('[name="update_cart"]').click();
    cy.get('.woocommerce-message', {timeout: 50000})
});

/**
 * Go To Checkout Page
 */
Cypress.Commands.add("goToCheckout", () => {
    cy.get('.checkout-button').click({force: true});
});

/**
 * Fill Billing from in checkout
 */
Cypress.Commands.add("fillBillingForm", (country) => {
    cy.get('#billing_first_name').clear({force: true});
    cy.get('#billing_last_name').clear({force: true});
    cy.get('#billing_address_1').clear({force: true});
    cy.get('#billing_postcode').clear({force: true});
    cy.get('#billing_city').clear({force: true});
    cy.get('#billing_phone').clear({force: true});
    cy.get('#billing_email').clear({force: true});

    let customerFixture = "customerFR";

    if (country !== undefined) {
        customerFixture = "customer" + country
    }

    cy.fixture(customerFixture).then((customer) => {
        cy.get('#billing_first_name').type(customer.firstName);
        cy.get('#billing_last_name').type(customer.lastName);
        cy.get('#billing_country').select(customer.country, {force: true});
        if (customer.state !== undefined) {
            if (!["BR", "MX"].includes(country)) {
                cy.get('#billing_state').clear({force: true});
                cy.get('#billing_state').type(customer.state);
            } else {
                cy.get('#billing_state').select(customer.state, {force: true});
            }
        }
        cy.get('#billing_address_1').type(customer.streetAddress);
        cy.get('#billing_postcode').type(customer.zipCode);
        cy.get('#billing_city').type(customer.city);
        cy.waitOrderUpdate();
        cy.get('#billing_phone').type(customer.phone);

        cy.get('#billing_email').type(customer.email);
    });
});

/**
 * Check page for redirection sucess
 */
Cypress.Commands.add("checkOrderSuccess", () => {
    cy.location('pathname', {timeout: 50000}).should('include', '/checkout/order-received');
});

/**
 * Check page for order cancelled
 */
Cypress.Commands.add("checkOrderCancelled", () => {
    cy.location('pathname', {timeout: 50000}).should('include', '/cart/');
    cy.get('.woocommerce-info').contains("Your order was cancelled");
});

/**
 * Check Payment refused
 */
Cypress.Commands.add("checkPaymentRefused", () => {
    cy.location('pathname', {timeout: 50000}).should('include', '/checkout/');
    cy.get('.woocommerce-error', {timeout: 50000}).contains(
        "Sorry, your payment has been declined. Please try again with an other means of payment."
    );
});

/**
 *
 */
Cypress.Commands.add("checkHostedFieldsError", (msg) => {
    cy.location('pathname', {timeout: 50000}).should('include', '/checkout/');
    cy.get('#error-js', {timeout: 50000}).contains(
        msg
    );
});

/**
 *
 */
Cypress.Commands.add("checkUnsupportedPayment", () => {
    cy.checkHostedFieldsError("This credit card type or the order currency is not supported.");
});

/**
 *
 */
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

/**
 *
 */
Cypress.Commands.add("saveLastCapturedOrderId", () => {
    cy.fixture('order').then((order) => {
        order.lastCapturedOrderId = order.lastOrderId;
        cy.writeFile('cypress/fixtures/order.json', order);
    });
});

/**
 * Process complete checkout and pay for test with basket
 */
Cypress.Commands.add("processTransactionWithBasket", () => {
    cy.configureAndActivateHostedFields();
    cy.get('#activate_basket').check();
    cy.saveConfigurationAndLogOut();
    cy.selectSeveralItemsAndGoToCart();
    cy.addProductQuantityForSeveralItems(15);
    cy.addAndApplyCouponCode();
    cy.proceedToCheckout('visa_ok');
    cy.checkOrderSuccess();
    cy.saveLastOrderId();
});

Cypress.Commands.add("payAndCheck", (paymentFunction, urlConst, paymentProduct) => {

    let skipProviderPage = Cypress.env('skipProviderPage');

    if (Cypress.env('completeProviderPayment') && !skipProviderPage.includes(paymentProduct)) {
        cy[paymentFunction]();
        cy.checkOrderSuccess();
    } else {
        cy.location({timeout: 100000}).should((loc) => {
            expect(loc.href.toLowerCase()).to.include(urlConst.toLowerCase());
        });
    }
});

Cypress.Commands.add("waitOrderUpdate", () => {
    cy.server();
    cy.route('POST', "/?wc-ajax=update_order_review").as("updateOrder");
    cy.wait("@updateOrder");
});

Cypress.Commands.add("customerLogIn", () => {
    cy.fixture('customerFR').then((customer) => {
        cy.visit('/my-account/');
        cy.get('#username').type(customer.email);
        cy.get('#password').type(customer.password);
        cy.get('[name="login"]').click();
    });
});
