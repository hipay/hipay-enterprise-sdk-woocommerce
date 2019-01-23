//import yandexJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/yandex.json';

describe('Pay by Klarna', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("klarna");
        cy.switchWooCurrency("EUR");
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
        cy.fillBillingForm("DE");
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('Pay by Klarna', function () {

        cy.get('.payment_method_hipayenterprise_klarna > label').click({force: true});
        cy.get('#place_order').click({force: true});
   //     cy.payAndCheck('payYandex', yandexJson.url, "yandex");
    });
});
