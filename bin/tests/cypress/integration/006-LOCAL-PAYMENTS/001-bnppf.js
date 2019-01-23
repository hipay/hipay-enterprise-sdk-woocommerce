import bnppfJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/bnppf.json';

describe('Pay by bnppf', function () {

    before(function () {
        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("bnpp-3xcb");
        cy.activatePaymentMethods("bnpp-4xcb");
        cy.switchWooCurrency("EUR");
        cy.adminLogOut();
    });

    beforeEach(function () {
        cy.selectItemAndGoToCart();
        cy.addProductQuantity(10);
        cy.goToCheckout();
        cy.fillBillingForm();
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('pay 3xcb', function () {

        cy.get('.payment_method_hipayenterprise_bnpp-3xcb > label').click({force: true});
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payBnppf', bnppfJson.url, "bnppf");
    });

    it('pay 4xcb', function () {

        cy.get('.payment_method_hipayenterprise_bnpp-4xcb > label').click();
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payBnppf', bnppfJson.url, "bnppf");
    });
});
