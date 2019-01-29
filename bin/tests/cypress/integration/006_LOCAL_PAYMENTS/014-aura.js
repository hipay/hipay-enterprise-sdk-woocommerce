import auraJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/aura.json';
import bradescoJson from "@hipay/hipay-cypress-utils/fixtures/payment-means/bradesco";

describe('Pay by Aura', function () {

    before(function () {
        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("aura");
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

    it('Pay by Aura', function () {

        cy.waitOrderUpdate();
        cy.get('[for="payment_method_hipayenterprise_aura"]').click({force: true});
        cy.get('#aura-national_identification_number')
            .type(auraJson.data.national_identification_number, {force: true})
            .should('have.value', auraJson.data.national_identification_number);
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payAura', auraJson.url, "aura");
    });
});
