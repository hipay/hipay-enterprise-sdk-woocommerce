import sofortJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/sofort.json';

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

        cy.get('[for="payment_method_hipayenterprise_sofort_uberweisung"]').click({force: true});
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('paySofort', sofortJson.url, "sofort");
    });
});
