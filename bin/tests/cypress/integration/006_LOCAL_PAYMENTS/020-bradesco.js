import bradescoJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/bradesco.json';
import boletoBancarioJson from "@hipay/hipay-cypress-utils/fixtures/payment-means/boleto-bancario";

describe('Pay by Bradesco', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("bradesco");
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

    it('Pay by Bradesco', function () {

        cy.get('[for="payment_method_hipayenterprise_bradesco"]').click({force: true});
        cy.get('#bradesco-national_identification_number')
            .type(bradescoJson.data.national_identification_number, {force: true})
            .should('have.value', bradescoJson.data.national_identification_number);
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payBradesco', bradescoJson.url, "bradesco");
    });
});
