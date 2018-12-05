describe('Pay by credit card hosted', function () {

    before(function () {
        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("credit_card");
        cy.goToAdminHipayConfig();

        cy.get('#methods-tab').click();
        cy.get('#operating_mode').select("hosted_page");
        cy.get('#display_hosted_page').select("redirect");

        cy.resetCCConfigForm();

        cy.get('.submit > .button-primary').click();

        cy.adminLogOut();
    });

    beforeEach(function () {

        cy.selectItemAndGoToCart();
        cy.addProductQuantity(15);
        cy.goToCheckout();
        cy.fillBillingForm();

        cy.get('.payment_method_hipayenterprise_credit_card > label').click();
        cy.get('#place_order').click();
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('Pay by visa', function () {
        cy.payCcHosted("visa");
        cy.checkOrderSuccess();
    });

    it('Pay by visa refused', function () {
        cy.payCcHosted("visaRefused");
        cy.checkOrderCancelled();
    });
});
