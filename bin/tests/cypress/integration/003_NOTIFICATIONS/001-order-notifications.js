describe('Send order notifications', function () {

    beforeEach(function () {
        cy.connectAndSelectAccountOnHipayBO();
        cy.fixture('order').as("order");
        cy.fixture('notification').as("notification");
    });

    afterEach(() => {
        cy.saveLastCapturedOrderId();
    });

    it('Send authorization notification', function () {
        cy.openTransactionOnHipayBO(this.order.lastOrderId + "-");
        cy.openNotificationOnHipayBO(116).then(() => {
            cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
        });
    });

    it('Send capture requested notification', function () {
        cy.openTransactionOnHipayBO(this.order.lastOrderId + "-");
        cy.openNotificationOnHipayBO(117).then(() => {
            cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
        });
    });

    it('Send captured notification', function () {
        cy.openTransactionOnHipayBO(this.order.lastOrderId + "-");
        cy.openNotificationOnHipayBO(118).then(() => {
            cy.sendNotification(this.notification.url, {data: this.data, hash: this.hash});
        });
    });
});
