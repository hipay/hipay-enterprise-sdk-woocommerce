import caixaJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/caixa.json';

describe('Pay by Caïxa', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("caixa");
        cy.switchWooCurrency("BRL");
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
        cy.fillBillingForm("BR");
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('Pay by Caïxa', function () {

        cy.get('[for="payment_method_hipayenterprise_caixa"]').click({force: true});
        cy.get('#caixa-national_identification_number').type(caixaJson.data.national_identification_number, {force: true});
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payCaixa', caixaJson.url, "caixa");
    });
});
