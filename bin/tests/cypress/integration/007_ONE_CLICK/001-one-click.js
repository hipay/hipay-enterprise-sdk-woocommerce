describe('Pay by credit card One click', function () {

    it('Pay by : visa_ok', function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("credit_card");
        cy.goToAdminHipayConfig();

        cy.get('#methods-tab').click();
        cy.get('#operating_mode').select("hosted_fields");
        cy.get('#card_token').check();

        cy.resetCCConfigForm();

        cy.get('.submit > .button-primary').click();

        cy.adminLogOut();

        cy.customerLogIn();
        cy.goToFront();
        cy.selectItemAndGoToCart();
        cy.addProductQuantity(15);
        cy.goToCheckout();
        cy.fillBillingForm();

        cy.get('[for="payment_method_hipayenterprise_credit_card"]').click({force: true});
        cy.get('#hipay-card-field-cardHolder > iframe');
        cy.wait(3000);

        cy.fill_hostedfield_card('visa_ok');
        cy.get("#wc-hipayenterprise_credit_card-new-payment-method").check();
        cy.get('#place_order').click({force: true});
        cy.checkOrderSuccess();
        cy.saveLastOrderId();
    });


    it('Send authorization notification', function () {
        cy.fixture('notification').as("notification");
        cy.connectAndSelectAccountOnHipayBO();
        cy.fixture('order').then((order) => {
            cy.openTransactionOnHipayBO(order.lastOrderId + "-");
            cy.openNotificationOnHipayBO(116).then(() => {
                cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
            });
        });
    });

    it('Check card in My account', function () {
        cy.customerLogIn();
        cy.get('.woocommerce-MyAccount-navigation-link--payment-methods').click();
        cy.get(".woocommerce-MyAccount-paymentMethods > tbody > tr:last-child > td:nth-child(1)")
            .contains("Visa ending in 411111******1111");

    });

    it('Pay by : one click hosted fields', function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("credit_card");
        cy.goToAdminHipayConfig();

        cy.get('#methods-tab').click();
        cy.get('#operating_mode').select("hosted_fields");
        cy.get('#card_token').check();

        cy.resetCCConfigForm();

        cy.get('.submit > .button-primary').click();

        cy.adminLogOut();

        cy.customerLogIn();
        cy.goToFront();
        cy.selectItemAndGoToCart();
        cy.addProductQuantity(15);
        cy.goToCheckout();
        cy.fillBillingForm();

        cy.get('[for="payment_method_hipayenterprise_credit_card"]').click({force: true});
        cy.get('#hipay-card-field-cardHolder > iframe');
        cy.wait(3000);

        cy.get('#place_order').click({force: true});
        cy.checkOrderSuccess();
        cy.saveLastOrderId();
    });

    it('Pay by : one click hosted page', function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("credit_card");
        cy.goToAdminHipayConfig();

        cy.get('#methods-tab').click();
        cy.get('#operating_mode').select("hosted_page");
        cy.get('#card_token').check();

        cy.resetCCConfigForm();

        cy.get('.submit > .button-primary').click();

        cy.adminLogOut();

        cy.customerLogIn();
        cy.goToFront();
        cy.selectItemAndGoToCart();
        cy.addProductQuantity(15);
        cy.goToCheckout();
        cy.fillBillingForm();

        cy.get('[for="payment_method_hipayenterprise_credit_card"]').click({force: true});

        cy.get('#place_order').click({force: true});
        cy.checkOrderSuccess();
        cy.saveLastOrderId();
    });

    // it('Add card in My account', function () {
    //     cy.customerLogIn();
    //     cy.get('.woocommerce-MyAccount-navigation-link--payment-methods').click();
    //     cy.get('.woocommerce-MyAccount-content > a').click();
    //
    //     cy.get('[for="payment_method_hipayenterprise_credit_card"]').click({force: true});
    //     cy.get('#hipay-card-field-cardHolder > iframe');
    //     cy.wait(3000);
    //     cy.fill_hostedfield_card('mastercard_ok');
    //     cy.get('#place_order').click();
    //     cy.get(".woocommerce-MyAccount-paymentMethods > tbody > tr:last-child > td:nth-child(1)")
    //         .contains("MasterCard ending in 539999******9999");
    // });
});
