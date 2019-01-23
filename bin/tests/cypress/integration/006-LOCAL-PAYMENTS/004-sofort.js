import sisalJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/sisal.json';

describe('Pay by Sofort', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("sofort_uberweisung");
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

    it('pay Sofort', function () {

        cy.get('.payment_method_hipayenterprise_sofort_uberweisung > label').click({force: true});
        cy.get('#place_order').click({force: true});
      //  cy.payAndCheck('paySisal', sisalJson.url, "sisal");
    });
});
