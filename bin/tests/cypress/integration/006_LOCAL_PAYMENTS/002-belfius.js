import belfiusJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/belfius.json';

describe('Pay by Belfius', function () {

    before(function () {
        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("dexia_directnet");
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

        cy.get('[for="payment_method_hipayenterprise_dexia_directnet"]').click({force: true});
        cy.wait(500);
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payBelfius', belfiusJson.url, "belfius");
    });
});
