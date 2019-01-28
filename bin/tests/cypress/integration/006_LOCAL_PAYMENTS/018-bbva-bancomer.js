import bbvaBancomerJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/bbva-bancomer.json';
import banamexJson from "@hipay/hipay-cypress-utils/fixtures/payment-means/banamex";

describe('Pay by BBVA Bancomer', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("bbva_bancomer");
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

    it('Pay by BBVA Bancomer', function () {

        cy.get('[for="payment_method_hipayenterprise_bbva_bancomer"]').click({force: true});
        cy.get('#bbva-bancomer-national_identification_number')
            .type(bbvaBancomerJson.data.national_identification_number, {force: true})
            .should('have.value', bbvaBancomerJson.data.national_identification_number);
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payBbvaBancomer', bbvaBancomerJson.url, "bbva-bancomer");
    });
});
