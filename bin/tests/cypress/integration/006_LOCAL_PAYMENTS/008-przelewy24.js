import przelewy24Json from '@hipay/hipay-cypress-utils/fixtures/payment-means/przelewy24.json';

describe('Pay by Przelewy24', function () {

    before(function () {

        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("przelewy24");
        cy.switchWooCurrency("PLN");
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
        cy.fillBillingForm("PL");
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('Pay by Przelewy24', function () {

        cy.get('[for="payment_method_hipayenterprise_przelewy24"]').click({force: true});
        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payPrzelewy24', przelewy24Json.url, "przelewy24");
    });
});
