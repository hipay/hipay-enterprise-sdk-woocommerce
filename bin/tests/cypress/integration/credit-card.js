describe('Pay by credit card', function () {

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

        cy.get('.payment_method_hipayenterprise_credit_card > label').click({force: true});
        cy.get('#hipay-field-cardHolder > iframe');
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

    it('Pay by Visa refused', function () {
        cy.fill_hostedfield_card('visa_refused');
        cy.get('#place_order').click({force: true});
        cy.checkPaymentRefused();
    });

    it('Pay by Mastercard', function () {
        cy.fill_hostedfield_card('mastercard_ok');
        cy.get('#place_order').click({force: true});
        cy.checkOrderSuccess();
    });

    it('Pay by CB', function () {
        cy.fill_hostedfield_card('cb_ok');
        cy.get('#place_order').click({force: true});
        cy.checkOrderSuccess();
    });

    it('Pay by Maestro', function () {
        cy.fill_hostedfield_card('maestro_ok');
        cy.get('#place_order').click({force: true});
        cy.checkOrderSuccess();
    });

    it('Pay by BCMC Unsupported', function () {
        cy.fill_hostedfield_card('bcmc_ok');
        cy.get('#place_order').click({force: true});
        cy.checkUnsupportedPayment();
    });

    it('Pay by American Express', function () {
        cy.fill_hostedfield_card('american-express_ok');
        cy.get('#place_order').click({force: true});
        cy.checkOrderSuccess();
    });
});
