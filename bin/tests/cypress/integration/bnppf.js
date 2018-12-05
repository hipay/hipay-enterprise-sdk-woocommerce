describe('Pay by bnppf', function () {

    beforeEach(function () {
        cy.logToAdmin();
        cy.goToPaymentsTab();
    });

    afterEach(() => {
        cy.saveLastOrderId();
    });

    it('pay 3xcb', function () {

        cy.activatePaymentMethods("bnpp-3xcb");
        cy.adminLogOut();
        cy.selectItemAndGoToCart();
        cy.addProductQuantity(15);
        cy.goToCheckout();
        cy.fillBillingForm();

        cy.get('.payment_method_hipayenterprise_bnpp-3xcb > label').click();
        cy.get('#place_order').click();
        cy.payBnppf();
        cy.checkOrderSuccess();
    });

    it('pay 4xcb', function () {

        cy.activatePaymentMethods("bnpp-4xcb");
        cy.adminLogOut();
        cy.selectItemAndGoToCart();
        cy.addProductQuantity(15);
        cy.goToCheckout();
        cy.fillBillingForm();

        cy.get('.payment_method_hipayenterprise_bnpp-4xcb > label').click();
        cy.get('#place_order').click();
        cy.payBnppf();
        cy.checkOrderSuccess();
    });
});
