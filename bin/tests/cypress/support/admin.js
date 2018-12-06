Cypress.Commands.add("logToAdmin", () => {
    cy.visit('/wp-admin');
    cy.wait(300);
    cy.get('#user_login').type("admin-wordpress@hipay.com");
    cy.get('#user_pass').type('hipay');
    cy.get('#wp-submit').click();

});

Cypress.Commands.add("adminLogOut", () => {
    cy.get('#wp-admin-bar-logout > .ab-item').click({force: true});
});

Cypress.Commands.add("goToPaymentsTab", () => {
    cy.get('#toplevel_page_woocommerce > .wp-submenu > :nth-child(5) > a').click({force: true});
    cy.get('[href="http://localhost:8000/wp-admin/admin.php?page=wc-settings&tab=checkout"]').click({force: true});
});

Cypress.Commands.add("goToAdminHipayConfig", () => {
    cy.get('[data-gateway_id="hipayenterprise_credit_card"] > .name > .wc-payment-gateway-method-title').click({force: true});
});

Cypress.Commands.add("activatePaymentMethods", (method) => {
    cy.get(
        '[data-gateway_id="hipayenterprise_' + method + '"] > .status > .wc-payment-gateway-method-toggle-enabled > .woocommerce-input-toggle'
    ).then(($btn) => {
        if ($btn.hasClass('woocommerce-input-toggle--disabled')) {
            $btn.click();
        }
    });
});

Cypress.Commands.add("goToOrderTab", () => {
    cy.get('#toplevel_page_woocommerce > .wp-submenu > li.wp-first-item > .wp-first-item').click({force: true});
});

Cypress.Commands.add("goToOrder", (id) => {
    cy.get('#post-' + id + ' > .order_number > .order-view > strong').click();
});

Cypress.Commands.add("checkAuthorizationStatusMessage", () => {
    cy.get(".note_content").contains("HiPay status: 116");
    cy.get(".note_content").contains("Order status changed from Pending payment to On hold.");
});

Cypress.Commands.add("checkCaptureStatusMessage", () => {
    cy.get(".note_content").contains("HiPay status: 117");
    cy.get(".note_content").contains("HiPay status: 118");
    cy.get(".note_content").contains("Order status changed from On hold to Completed.");
});

Cypress.Commands.add("resetCCConfigForm", () => {
    cy.get('#woocommerce_hipayenterprise_methods_visa_minAmount').clear({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_mastercard_minAmount').clear({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_cb_minAmount').clear({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_maestro_minAmount').clear({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_american-express_minAmount').clear({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_bcmc_minAmount').clear({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_visa_activated').check({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_mastercard_activated').check({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_cb_activated').check({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_maestro_activated').check({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_american-express_activated').check({force: true});
    cy.get('#woocommerce_hipayenterprise_methods_bcmc_activated').check({force: true});
});

Cypress.Commands.add("resetProductionConfigForm", () => {
    cy.get('#woocommerce_hipayenterprise_account_production_username').clear();
    cy.get('#woocommerce_hipayenterprise_account_production_password').clear();
    cy.get('#woocommerce_hipayenterprise_account_production_tokenjs_username').clear();
    cy.get('#woocommerce_hipayenterprise_account_production_password_publickey').clear();
    cy.get('#woocommerce_hipayenterprise_account_production_secret_passphrase').clear();
});
