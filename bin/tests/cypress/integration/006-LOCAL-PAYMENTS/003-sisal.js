import sisalJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/sisal.json';

describe('Pay by Sisal', function () {

    before(function () {
        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("sisal");
        cy.switchWooCurrency("EUR");
        cy.adminLogOut();
    });

    beforeEach(function () {
        cy.selectItemAndGoToCart();
        cy.addProductQuantity(2);
        cy.goToCheckout();
        cy.fillBillingForm();
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('pay Belfius', function () {

        cy.get('.payment_method_hipayenterprise_sisal > label').click({force: true});
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('paySisal', sisalJson.url, "sisal");
    });
});
