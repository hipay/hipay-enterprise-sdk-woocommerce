import ingHomepayJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/ing-homepay.json';

describe('Pay by Ing Homepay', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("ing_homepay");
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

    it('pay Ing Homepay', function () {

        cy.get('[for="payment_method_hipayenterprise_ing_homepay"]').click({force: true});
        cy.wait(500);
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payIngHomepay', ingHomepayJson.url, "ing-homepay");
    });
});
