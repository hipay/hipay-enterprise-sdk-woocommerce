import boletoBancarioJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/boleto-bancario.json';

describe('Pay by Boleto Bancario', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("boleto_bancario");
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

    it('Pay by Boleto Bancario', function () {

        cy.get('[for="payment_method_hipayenterprise_boleto_bancario"]').click({force: true});
        cy.get('#boleto-bancario-national_identification_number').type(boletoBancarioJson.data.national_identification_number, {force: true});
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payBoletoBancario', boletoBancarioJson.url, "boleto-bancario");
    });
});
