/**
 * Functionality tested
 *  - Set conf for manual capture ( Just authorization)
 *  - Do transaction with basket
 *  - Do several manual capture
 *  - Process transaction and check status order, order note
 *
 */
var utils = require('../../support/utils');
describe('Process transaction and do manual capture with basket', function () {

    beforeEach(function () {
        cy.fixture('order').as("order");
        cy.fixture('basket').as("basket");
        cy.fixture('notification').as("notification");
    });

    /**
     * Process an payment with mapping ( Transaction should be OK )
     */
    it('Succeed Transaction with mapping', function () {
        cy.configureAndSetCaptureMode("manual");
        cy.saveConfigurationAndLogOut();
        cy.processTransactionWithBasket();
    });

    /**
     * Send transaction for authorization
     */
    it('Check Basket in transaction', function () {
        cy.connectAndSelectAccountOnHipayBO();

        cy.openTransactionOnHipayBO(this.order.lastOrderId + "-");
        cy.openNotificationOnHipayBO(116).then(() => {
            cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
        });
    });

    /**
     * Do capture and process transaction
     */
    it('Process partial capture with basket', function () {
        const stub = cy.stub();
        cy.logAndGoToDetailOrder(this.order.lastOrderId);
        cy.checkAuthorizationStatusMessage();
        cy.get("#woocommerce-order-items  p.add-items > button.button.capture-items").click();
        cy.get("#order_line_items > tr:nth-child(1) > td.quantity > div.refund > input").type("5");
        cy.get("#order_line_items > tr:nth-child(3) > td.quantity > div.refund > input").type("7");
        cy.get("#order_line_items > tr:nth-child(5) > td.quantity > div.refund > input").type("9");
        cy.get("#order_line_items > tr:nth-child(1) > td.quantity > div.refund > input").focus();
        cy.get('#order_line_items > tr:nth-child(5) > td.line_cost > div.refund > input').should('have.value', '142,155');
        cy.get('#capture_amount', {timeout: 10000}).should('have.value', '382,24');
        cy.on('window:confirm', stub);
        cy.get('#hipay-capture-items > div.refund-actions > button.button.button-primary.do-api-capture').click();
        cy.get('#order_line_items > tr:nth-child(2) > .line_cost > .view > .captured > .woocommerce-Price-amount', {timeout: 10000}).contains('65,81');
        cy.get('#order_line_items > tr:nth-child(4) > .line_cost > .view > .captured > .woocommerce-Price-amount', {timeout: 10000}).contains('110,57');
        cy.get('#order_line_items > tr:nth-child(6) > .line_cost > .view > .captured > .woocommerce-Price-amount', {timeout: 10000}).contains('142,16');
        cy.get("#order_captures").contains("Capture");
    });

    /**
     * Send transaction for first capture
     */
    it('Send capture notification', function () {
        cy.log("sendNotification");
        cy.connectAndSelectAccountOnHipayBO();

        cy.openTransactionOnHipayBO(this.order.lastOrderId + "-");
        cy.openNotificationOnHipayBO(118).then(() => {
            cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
        });
    });

    /**
     * Check status order for partial and do the second capture
     */
    it('Process second partial capture with basket', function () {
        const stub = cy.stub();
        cy.logAndGoToDetailOrder(this.order.lastOrderId);
        cy.get('#order_status').should("have.value", "wc-partial-captured");
        cy.get('#woocommerce-order-notes > div > ul > li:nth-child(1)').contains("Registered notification from HiPay about captured amount of 382.24");
        cy.get("#woocommerce-order-items  p.add-items > button.button.capture-items").click();
        cy.get("#order_line_items > tr:nth-child(1) > td.quantity > div.refund > input").type("10");
        cy.get("#order_line_items > tr:nth-child(3) > td.quantity > div.refund > input").type("11");
        cy.get("#order_line_items > tr:nth-child(5) > td.quantity > div.refund > input").type("11");
        cy.get("#order_line_items > tr:nth-child(1) > td.quantity > div.refund > input").focus();
        cy.get('#capture_amount', {timeout: 10000}).should('have.value', '574,94');

        cy.on('window:confirm', stub);
        cy.get('#hipay-capture-items > div.refund-actions > button.button.button-primary.do-api-capture').click();
        cy.get("#woocommerce-order-items  p.add-items > button.button.capture-items", {timeout: 10000}).should('not.exist');
        cy.get('#order_line_items > tr:nth-child(2) > .line_cost > .view > .captured > .woocommerce-Price-amount', {timeout: 10000}).contains('197,44');
        cy.get('#order_line_items > tr:nth-child(4) > .line_cost > .view > .captured > .woocommerce-Price-amount', {timeout: 10000}).contains('284,31');
        cy.get('#order_line_items > tr:nth-child(6) > .line_cost > .view > .captured > .woocommerce-Price-amount', {timeout: 10000}).contains('315,90');
    });

    /**
     * Send second catpure
     */
    it('Send capture notification (2)', function () {
        cy.log("Capture 2");
        cy.connectAndSelectAccountOnHipayBO();

        cy.openTransactionOnHipayBO(this.order.lastOrderId + "-");
        cy.openNotificationOnHipayBO(118).then(() => {
            var basketTransaction = utils.fetchInput("basket", decodeURI(this.data));
            assert.equal(basketTransaction, JSON.stringify(this.basket.capture001));
            cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
        });
    });

    /**
     * Check Order status
     */
    it('Check Order status and note', function () {
        const stub = cy.stub();
        cy.logAndGoToDetailOrder(this.order.lastOrderId);
        cy.get('#order_status').should("have.value", "wc-processing");
        cy.get('#woocommerce-order-notes > div > ul > li:nth-child(1)').contains("Registered notification from HiPay about captured amount of 957.18");
    });
});
