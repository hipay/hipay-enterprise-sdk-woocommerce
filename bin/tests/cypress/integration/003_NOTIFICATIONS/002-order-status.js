describe('capture order', function () {

    beforeEach(function () {
        cy.fixture('order').as("order");
        cy.logToAdmin();
        cy.goToOrderTab();
    });

    it('Check order notification messages', function () {
        cy.goToDetailOrder(this.order.lastCapturedOrderId);
        cy.checkAuthorizationStatusMessage();
        cy.checkCaptureStatusMessage();
    });
});
