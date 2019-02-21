import idealJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/ideal.json';

describe('Pay by IDEAL', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("ideal");
        cy.switchWooCurrency("EUR");
        cy.adminLogOut();
    });

    beforeEach(function () {
        cy.selectItemAndGoToCart();
        cy.addProductQuantity(2);
        cy.goToCheckout();
        cy.fillBillingForm("NL");
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('Pay by IDEAL', function () {

        cy.get('[for="payment_method_hipayenterprise_ideal"]').click({force: true});
        cy.wait(3000);

        cy.fill_hostedfields_input("hipay-ideal-field-issuer_bank_id", idealJson.data.bic);

        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payIdeal', idealJson.url, "ideal");
    });
});
