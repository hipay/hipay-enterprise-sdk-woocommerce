import mybankJson from '@hipay/hipay-cypress-utils/fixtures/payment-means/mybank.json';

describe('Pay by MyBank', function () {

    before(function () {
        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("mybank");
        cy.switchWooCurrency("EUR");
        cy.adminLogOut();
    });

    beforeEach(function () {
        cy.selectItemAndGoToCart();
        cy.addProductQuantity(2);
        cy.goToCheckout();
    });
    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('Pay by MyBank', function () {

        cy.fillBillingForm("IT");

        cy.get('[for="payment_method_hipayenterprise_mybank"]').click({force: true});
        cy.wait(3000);

        cy.get('#place_order').click({force: true});
        cy.payAndCheck('payMybank', mybankJson.url, "mybank");
    });

    it('Pay by MyBank error', function () {

        cy.fillBillingForm("FR");

        cy.get('[for="payment_method_hipayenterprise_mybank"]').should('not.exist');
    });

});
