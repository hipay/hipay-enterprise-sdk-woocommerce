// import klarnaJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/klarna.json';

describe('Pay by Klarna', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("klarnainvoice");
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

        cy.get('[for="payment_method_hipayenterprise_klarnainvoice"]').click({force: true});
        cy.wait(500);
        cy.get('#place_order').click({force: true});
        // cy.payAndCheck('payKlarna', klarnaJson.url, "klarna");
    });
});
