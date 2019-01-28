import banamexJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/banamex.json';
import auraJson from "@hipay/hipay-cypress-utils/fixtures/payment-means/aura";

describe('Pay by Banamex', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("banamex");
        cy.switchWooCurrency("MXN");
        cy.adminLogOut();
    });

    after(function () {

        cy.logToAdmin();
        cy.switchWooCurrency("EUR");
        cy.adminLogOut();
    });

    beforeEach(function () {
        cy.selectItemAndGoToCart();
        cy.addProductQuantity(2);
        cy.goToCheckout();
        cy.fillBillingForm("MX");
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('Pay by Banamex', function () {

        cy.get('[for="payment_method_hipayenterprise_banamex"]').click({force: true});
        cy.get('#banamex-national_identification_number')
            .type(banamexJson.data.national_identification_number, {force: true})
            .should('have.value', banamexJson.data.national_identification_number);
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payBanamex', banamexJson.url, "banamex");
    });
});
