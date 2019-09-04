describe('Create order', function () {

    before(function () {
        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("credit_card");
        cy.goToAdminHipayConfig();

        cy.get('#methods-tab').click();
        cy.get('#operating_mode').select("hosted_fields");

        cy.resetCCConfigForm();

        cy.get('.submit > .button-primary').click();

        cy.adminLogOut();
    });

    beforeEach(function () {

        cy.selectItemAndGoToCart();
        cy.addProductQuantity(15);
        cy.goToCheckout();
        cy.fillBillingForm();

        cy.get('[for="payment_method_hipayenterprise_credit_card"]').click({force: true});
        cy.get('#hipay-card-field-cardHolder > iframe');
        cy.wait(3000);
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('Pay by Visa', function () {
        cy.fill_hostedfield_card('visa_ok');
        cy.get('#place_order').click({force: true});
        cy.checkOrderSuccess();
    });
});
