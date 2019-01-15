describe('Change credit card configuration', function () {

    beforeEach(function () {
        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("credit_card");
        cy.goToAdminHipayConfig();
        cy.get('#methods-tab').click();
        cy.resetCCConfigForm();
        cy.get('.submit > .button-primary').click();
    });

    it('Should not be available', function () {
        cy.get('#woocommerce_hipayenterprise_methods_visa_minAmount').clear({force: true});
        cy.get('#woocommerce_hipayenterprise_methods_mastercard_minAmount').clear({force: true});
        cy.get('#woocommerce_hipayenterprise_methods_cb_minAmount').clear({force: true});
        cy.get('#woocommerce_hipayenterprise_methods_maestro_minAmount').clear({force: true});
        cy.get('#woocommerce_hipayenterprise_methods_american-express_minAmount').clear({force: true});
        cy.get('#woocommerce_hipayenterprise_methods_bcmc_minAmount').clear({force: true});
        cy.get('#woocommerce_hipayenterprise_methods_visa_minAmount').type(150, {force: true});
        cy.get('#woocommerce_hipayenterprise_methods_mastercard_minAmount').type(150, {force: true});
        cy.get('#woocommerce_hipayenterprise_methods_cb_minAmount').type(150, {force: true});
        cy.get('#woocommerce_hipayenterprise_methods_maestro_minAmount').type(150, {force: true});
        cy.get('#woocommerce_hipayenterprise_methods_american-express_minAmount').type(150, {force: true});
        cy.get('#woocommerce_hipayenterprise_methods_bcmc_minAmount').type(150, {force: true});

        cy.get('.submit > .button-primary').click();

        cy.get('#woocommerce_hipayenterprise_methods_visa_minAmount').should('have.value', '150');
        cy.get('#woocommerce_hipayenterprise_methods_mastercard_minAmount').should('have.value', '150');
        cy.get('#woocommerce_hipayenterprise_methods_cb_minAmount').should('have.value', '150');
        cy.get('#woocommerce_hipayenterprise_methods_maestro_minAmount').should('have.value', '150');
        cy.get('#woocommerce_hipayenterprise_methods_american-express_minAmount').should('have.value', '150');
        cy.get('#woocommerce_hipayenterprise_methods_bcmc_minAmount').should('have.value', '150');

        cy.adminLogOut();
        cy.selectItemAndGoToCart();
        cy.addProductQuantity(5);
        cy.goToCheckout();
        cy.fillBillingForm();
        cy.get('.payment_method_hipayenterprise_credit_card').should('not.exist');
    });

    it('Should be available', function () {
        cy.get('#woocommerce_hipayenterprise_methods_visa_minAmount').clear({force: true});
        cy.get('#woocommerce_hipayenterprise_methods_mastercard_minAmount').clear({force: true});
        cy.get('#woocommerce_hipayenterprise_methods_cb_minAmount').clear({force: true});
        cy.get('#woocommerce_hipayenterprise_methods_maestro_minAmount').clear({force: true});
        cy.get('#woocommerce_hipayenterprise_methods_american-express_minAmount').clear({force: true});
        cy.get('#woocommerce_hipayenterprise_methods_bcmc_minAmount').clear({force: true});
        cy.get('#woocommerce_hipayenterprise_methods_visa_minAmount').type(0, {force: true});
        cy.get('#woocommerce_hipayenterprise_methods_mastercard_minAmount').type(150, {force: true});
        cy.get('#woocommerce_hipayenterprise_methods_cb_minAmount').type(150, {force: true});
        cy.get('#woocommerce_hipayenterprise_methods_maestro_minAmount').type(150, {force: true});
        cy.get('#woocommerce_hipayenterprise_methods_american-express_minAmount').type(150, {force: true});
        cy.get('#woocommerce_hipayenterprise_methods_bcmc_minAmount').type(150, {force: true});

        cy.get('.submit > .button-primary').click();

        cy.get('#woocommerce_hipayenterprise_methods_visa_minAmount').should('have.value', '0');
        cy.get('#woocommerce_hipayenterprise_methods_mastercard_minAmount').should('have.value', '150');
        cy.get('#woocommerce_hipayenterprise_methods_cb_minAmount').should('have.value', '150');
        cy.get('#woocommerce_hipayenterprise_methods_maestro_minAmount').should('have.value', '150');
        cy.get('#woocommerce_hipayenterprise_methods_american-express_minAmount').should('have.value', '150');
        cy.get('#woocommerce_hipayenterprise_methods_bcmc_minAmount').should('have.value', '150');

        cy.adminLogOut();
        cy.selectItemAndGoToCart();
        cy.addProductQuantity(5);
        cy.goToCheckout();
        cy.fillBillingForm();
        cy.get('.payment_method_hipayenterprise_credit_card').should('exist');
    });

    it('Should not be available', function () {
        cy.get('#woocommerce_hipayenterprise_methods_visa_activated').uncheck({force: true});
        cy.get('#woocommerce_hipayenterprise_methods_mastercard_activated').uncheck({force: true});
        cy.get('#woocommerce_hipayenterprise_methods_cb_activated').uncheck({force: true});
        cy.get('#woocommerce_hipayenterprise_methods_maestro_activated').uncheck({force: true});
        cy.get('#woocommerce_hipayenterprise_methods_american-express_activated').uncheck({force: true});
        cy.get('#woocommerce_hipayenterprise_methods_bcmc_activated').uncheck({force: true});

        cy.get('.submit > .button-primary').click();


        cy.get('#woocommerce_hipayenterprise_methods_visa_activated').not('be.checked');
        cy.get('#woocommerce_hipayenterprise_methods_mastercard_activated').not('be.checked');
        cy.get('#woocommerce_hipayenterprise_methods_cb_activated').not('be.checked');
        cy.get('#woocommerce_hipayenterprise_methods_maestro_activated').not('be.checked');
        cy.get('#woocommerce_hipayenterprise_methods_american-express_activated').not('be.checked');
        cy.get('#woocommerce_hipayenterprise_methods_bcmc_activated').not('be.checked');

        cy.adminLogOut();
        cy.selectItemAndGoToCart();
        cy.addProductQuantity(5);
        cy.goToCheckout();
        cy.fillBillingForm();
        cy.get('.payment_method_hipayenterprise_credit_card').should('not.exist');
    });
});
