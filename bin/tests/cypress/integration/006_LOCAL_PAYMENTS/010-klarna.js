// import klarnaJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/klarna.json';

describe('Pay by Klarna', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("klarna");
        cy.switchWooCurrency("EUR");
        cy.adminLogOut();
    });

    beforeEach(function () {
        cy.selectItemAndGoToCart();
        cy.addProductQuantity(2);
        cy.goToCheckout();
        cy.fillBillingForm("DE");
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('Pay by Klarna', function () {

        cy.get('[for="payment_method_hipayenterprise_klarna"]').click({force: true});
        cy.get('#place_order').click({force: true});
        // cy.payAndCheck('payKlarna', klarnaJson.url, "klarna");
    });
});
