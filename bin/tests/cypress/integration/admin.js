describe('Check admin', function () {

    beforeEach(function () {
        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.goToAdminHipayConfig();
        cy.resetProductionConfigForm();
        cy.get('.submit > .button-primary').click();
    });

    it('Should not save credentials', function () {
        cy.get('#woocommerce_hipayenterprise_account_production_username').type("test");
        cy.get('#woocommerce_hipayenterprise_account_production_password_publickey').type("test");
        cy.get('.submit > .button-primary').click();

        cy.get('#setting-error- > p > strong').contains("If production api username is filled production api password is mandatory");
    });

    it('Should save credentials', function () {
        cy.get('#woocommerce_hipayenterprise_account_production_username').type("test");
        cy.get('#woocommerce_hipayenterprise_account_production_password').type("test");

        cy.get('#woocommerce_hipayenterprise_account_production_tokenjs_username').type("test");
        cy.get('#woocommerce_hipayenterprise_account_production_password_publickey').type("test");

        cy.get('#woocommerce_hipayenterprise_account_production_secret_passphrase').type("test");

        cy.get('.submit > .button-primary').click();

        cy.get('#woocommerce_hipayenterprise_account_production_username').should('have.value', "test");
        cy.get('#woocommerce_hipayenterprise_account_production_password').should('have.value', "test");

        cy.get('#woocommerce_hipayenterprise_account_production_tokenjs_username').should('have.value', "test");
        cy.get('#woocommerce_hipayenterprise_account_production_password_publickey').should('have.value', "test");

        cy.get('#woocommerce_hipayenterprise_account_production_secret_passphrase').should('have.value', "test");

    });

});
