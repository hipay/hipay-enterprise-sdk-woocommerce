import yandexJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/yandex.json';

describe('Pay by Yandex Money', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("yandex");
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

    it('Pay by Yandex Money', function () {

        cy.get('[for="payment_method_hipayenterprise_yandex"]').click({force: true});
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payYandex', yandexJson.url, "yandex");
    });
});
