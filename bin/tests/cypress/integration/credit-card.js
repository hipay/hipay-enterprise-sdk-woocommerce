describe('Pay by credit card', function () {

    beforeEach(function () {
        cy.logToAdmin();
        cy.goToPaymentsTab();
        cy.activatePaymentMethods("credit_card");
        cy.adminLogOut();
        cy.selectItemAndGoToCart();
        cy.goToCheckout();
        cy.fillBillingForm();
    });

    it('Should not save credentials', function () {

    });
});
