import webmoneyJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/webmoney.json';

describe('Pay by WebMoney Transfer', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("webmoney_transfert");
        cy.switchWooCurrency("RUB");
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
        cy.fillBillingForm("RU");
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('Pay by WebMoney Transfer', function () {

        cy.get('[for="payment_method_hipayenterprise_webmoney_transfert"]').click({force: true});
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payWebmoney', webmoneyJson.url, "webmoney");
    });
});
