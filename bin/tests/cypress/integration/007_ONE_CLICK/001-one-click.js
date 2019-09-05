describe('Pay by credit card One click', function () {

    it('Pay by : visa_ok', function () {
        cy.logToAdmin();
        cy.addClient();
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

    ['hosted_fields', 'hosted_page'].forEach((operating_mode) => {
        it('Add card in My account and Pay' + operating_mode, function () {

            cy.logToAdmin();
            cy.goToPaymentsTab();
            cy.activatePaymentMethods("credit_card");
            cy.goToAdminHipayConfig();

            cy.get('#methods-tab').click();
            cy.get('#operating_mode').select(operating_mode);
            cy.get('#card_token').check();

            cy.resetCCConfigForm();

            cy.get('.submit > .button-primary').click();

            cy.adminLogOut();

            cy.customerLogIn();
            cy.get('.woocommerce-MyAccount-navigation-link--payment-methods').click();
            cy.get('.woocommerce-MyAccount-content > a').click();

            cy.get('[for="payment_method_hipayenterprise_credit_card"]').click({force: true});
            cy.get('#hipay-card-field-cardHolder > iframe');
            cy.wait(3000);
            cy.fill_hostedfield_card('mastercard_ok');
            cy.get('#place_order').click();
            cy.get(".woocommerce-MyAccount-paymentMethods > tbody > tr:last-child > td:nth-child(1)")
                .contains("MasterCard ending in 539999******9999");

            cy.selectItemAndGoToCart();
            cy.addProductQuantity(15);
            cy.goToCheckout();
            cy.fillBillingForm();

            cy.get('[for="payment_method_hipayenterprise_credit_card"]').click({force: true});

            cy.contains("MasterCard 539999******9999 (expires").click({force: true});
            cy.get(".hipay-cvv-oneclick").type("111");
            cy.get(".hipay-token-update").click({force: true});
            cy.get(".woocommerce-hipay-success").contains("Card updated with success");

            cy.get('#place_order').click({force: true});
            cy.checkOrderSuccess();
            cy.saveLastOrderId();
        });
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

    it('Pay with validated card', function () {
        cy.customerLogIn();
        cy.selectItemAndGoToCart();
        cy.addProductQuantity(15);
        cy.goToCheckout();
        cy.fillBillingForm();

        cy.get('[for="payment_method_hipayenterprise_credit_card"]').click({force: true});

        cy.contains("MasterCard 539999******9999 (expires").click({force: true});

        cy.get('#place_order').click({force: true});
        cy.checkOrderSuccess();
        cy.saveLastOrderId();
    });

    it('Add card in My account and Pay error in form', function () {
        cy.customerLogIn();
        cy.get('.woocommerce-MyAccount-navigation-link--payment-methods').click();
        cy.get('.woocommerce-MyAccount-content > a').click();

        cy.get('[for="payment_method_hipayenterprise_credit_card"]').click({force: true});
        cy.get('#hipay-card-field-cardHolder > iframe');
        cy.wait(3000);
        cy.fill_hostedfield_card('mastercard_ok');
        cy.get('#place_order').click();
        cy.get(".woocommerce-MyAccount-paymentMethods > tbody > tr:last-child > td:nth-child(1)")
            .contains("MasterCard ending in 539999******9999");

        cy.selectItemAndGoToCart();
        cy.addProductQuantity(15);
        cy.goToCheckout();
        cy.fillBillingForm();

        cy.get('[for="payment_method_hipayenterprise_credit_card"]').click({force: true});

        cy.contains("MasterCard 539999******9999 (expires").click({force: true});
        cy.get(".hipay-token-update").click({force: true});
        cy.get(".hipay-field-error").contains("CVC is missing.");

        cy.get(".hipay-cvv-oneclick").type("1");

        cy.get('#place_order').click({force: true});

        cy.get(".hipay-field-error").contains("CVC is invalid.");

    });

    it('Add non recurring card in My account', function () {
        cy.customerLogIn();
        cy.get('.woocommerce-MyAccount-navigation-link--payment-methods').click();
        cy.get('.woocommerce-MyAccount-content > a').click();

        cy.get('[for="payment_method_hipayenterprise_credit_card"]').click({force: true});
        cy.get('#hipay-card-field-cardHolder > iframe');
        cy.wait(3000);
        cy.fill_hostedfield_card('maestro_ok');
        cy.get('#place_order').click();

        cy.get('.woocommerce-error').contains("Unable to add payment method to your account.");
    });

    ['hosted_fields', 'hosted_page'].forEach((operating_mode) => {
        it('Add card in My account and Pay' + operating_mode, function () {

            cy.logToAdmin();
            cy.goToPaymentsTab();
            cy.activatePaymentMethods("credit_card");
            cy.goToAdminHipayConfig();

            cy.get('#methods-tab').click();
            cy.get('#operating_mode').select(operating_mode);
            cy.get('#display_hosted_page').select("redirect", {force: true});
            cy.get('#card_token').check();

            cy.resetCCConfigForm();

            cy.get('.submit > .button-primary').click();

            cy.adminLogOut();

            cy.customerLogIn();
            cy.get('.woocommerce-MyAccount-navigation-link--payment-methods').click();
            cy.get('.woocommerce-MyAccount-content > a').click();

            cy.get('[for="payment_method_hipayenterprise_credit_card"]').click({force: true});
            cy.get('#hipay-card-field-cardHolder > iframe');
            cy.wait(3000);
            cy.fill_hostedfield_card('mastercard_ok');
            cy.get('#place_order').click();
            cy.get(".woocommerce-MyAccount-paymentMethods > tbody > tr:last-child > td:nth-child(1)")
                .contains("MasterCard ending in 539999******9999");

            cy.selectItemAndGoToCart();
            cy.addProductQuantity(15);
            cy.goToCheckout();
            cy.fillBillingForm();

            cy.get('[for="payment_method_hipayenterprise_credit_card"]').click({force: true});
            cy.get('#wc-hipayenterprise_credit_card-payment-token-new').click({force: true});


            cy.get('#place_order').click({force: true});

            if(operating_mode === 'hosted_fields' ){
                cy.checkHostedFieldsInlineError("Card number is missing.", "card", "cardNumber");
                cy.fill_hostedfield_card('visa_ok');
                cy.get('#place_order').click({force: true});
            }else{
                cy.payCcHostedWithHF("visa_ok");
            }

            cy.checkOrderSuccess();
            cy.saveLastOrderId();
        });
    });

});
