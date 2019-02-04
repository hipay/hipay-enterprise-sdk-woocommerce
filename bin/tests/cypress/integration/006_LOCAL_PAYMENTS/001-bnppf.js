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


    ['3xcb', '4xcb'].forEach((bnpMethod) => {
        it('Pay by : ' + bnpMethod, function () {
            cy.get('[for="payment_method_hipayenterprise_bnpp-' + bnpMethod + '"]').click({force: true});
            cy.get('#place_order').click({force: true});
            cy.payAndCheck('payBnppf', bnppfJson.url, "bnppf");
        });
    });
});
