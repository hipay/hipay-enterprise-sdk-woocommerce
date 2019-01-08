/**
 * Functionality tested
 *  - Process transaction and check status order, order note
 *  - Refund with basket
 *
 */
var utils= require('../../support/utils');
describe('Process transaction and do manual refund with basket', function () {

    beforeEach(function () {
        cy.fixture('order').as("order");
        cy.fixture('basket').as("basket");
        cy.fixture('notification').as("notification");
    });

    /**
     * Process an payment with mapping ( Transaction should be OK )
     */
    it('Succeed Transaction with mapping', function () {
        cy.configureAndSetCaptureMode("automatic");
        cy.saveConfigurationAndLogOut();
        cy.processTransactionWithBasket();
    });

    /**
     * Send transaction for authorization
     */
    it('Check 116 and 118 Transactions', function () {
        cy.clearCookies();
        cy.HiPayBOConnect();
        cy.HiPayBOSelectAccount();

        cy.HiPayBOGoToTransaction(this.order.lastOrderId + "-");
        cy.HiPayBOOpenNotifications(116).then(() => {
            cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});

            cy.reload();
            cy.HiPayBOOpenNotifications(118).then(() => {
                cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
            });
        });
    });

    /**
     * Do refund
     */
    it('Process partial refund with basket', function () {
        const stub = cy.stub();
        cy.logAndGoToDetailOrder(this.order.lastOrderId);
        cy.checkAuthorizationStatusMessage();
        cy.get("#woocommerce-order-items  p.add-items > button.button.refund-items").click();
        cy.get("#order_line_items > tr:nth-child(1) > td.quantity > div.refund > input").type("5");
        cy.get("#order_line_items > tr:nth-child(3) > td.quantity > div.refund > input").type("7");
        cy.get("#order_line_items > tr:nth-child(5) > td.quantity > div.refund > input").type("9");
        cy.get("#order_line_items > tr:nth-child(1) > td.quantity > div.refund > input").focus();
        cy.get('#order_line_items > tr:nth-child(5) > td.line_cost > div.refund > input').should('have.value','142,155');
        cy.get('#refund_amount', { timeout: 10000 }).should('have.value', '382,24');
        cy.on('window:confirm', stub);
        cy.get('.do-api-refund').click();
        cy.get('#order_line_items > tr:nth-child(1) > td.line_cost > div.view > small > span',{ timeout: 20000 }).contains('65,81');
        cy.get('#order_line_items > tr:nth-child(3) > td.line_cost > div.view > small > span',{ timeout: 20000 }).contains('110,57');
        cy.get('#order_line_items > tr:nth-child(5) > td.line_cost > div.view > small > span',{ timeout: 20000 }).contains('142,16');
    });

    /**
     * Send transaction for Refund Requested
     */
    it('Check 124 Transaction', function () {
        cy.clearCookies();
        cy.HiPayBOConnect();
        cy.HiPayBOSelectAccount();

        cy.HiPayBOGoToTransaction(this.order.lastOrderId + "-");
        cy.HiPayBOOpenNotifications(124).then(() => {
            cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
        });
    });

    /**
     * Process refund withoit
     */
    it('Process partial without basket', function () {
        const stub = cy.stub();
        cy.logAndGoToDetailOrder(this.order.lastOrderId);
        cy.on('window:confirm', stub);
        cy.get("#woocommerce-order-items  p.add-items > button.button.refund-items").click();
        // Invalid Refund
        cy.get('#refund_amount', { timeout: 10000 }).type('900');
        cy.get('.do-api-refund').click();
        cy.wait(5000);
        cy.get("#woocommerce-order-items  p.add-items > button.button.refund-items",{ timeout: 10000 }).click();
        cy.get('#refund_amount', { timeout: 10000 }).clear();
        cy.get('#refund_amount', { timeout: 10000 }).type('574,94');
        cy.get('.do-api-refund').click();
        cy.get('#order_status').should("have.value", "wc-refunded");
    });

    /**
     * Check basket is not sent
     */
    it('Check basket transaction', function () {
        cy.HiPayBOConnect();
        cy.HiPayBOSelectAccount();

        cy.HiPayBOGoToTransaction(this.order.lastOrderId + "-");
        cy.HiPayBOOpenNotifications(124).then(() => {
            var basketTransaction = utils.fetchInput("basket",decodeURI(this.data));
            assert.equal(basketTransaction,"");
        });
    });

});
