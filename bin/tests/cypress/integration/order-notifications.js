describe('Send order notifications', function () {

    beforeEach(function () {
        cy.HiPayBOConnect();
        cy.HiPayBOSelectAccount();
        cy.fixture('order').as("order");
    });

    afterEach(() => {
        cy.saveLastCapturedOrderId();
    });

    it('Send authorization notification', function () {
        cy.HiPayBOGoToTransaction(this.order.lastOrderId + "-");
        cy.HiPayBOOpenNotifications(116).then(() => {
            cy.sendNotification({data: this.data, hash: this.hash});
        });
    });

    it('Send capture requested notification', function () {
        cy.HiPayBOGoToTransaction(this.order.lastOrderId + "-");
        cy.HiPayBOOpenNotifications(117).then(() => {
            cy.sendNotification({data: this.data, hash: this.hash});
        });
    });

    it('Send captured notification', function () {
        cy.HiPayBOGoToTransaction(this.order.lastOrderId + "-");
        cy.HiPayBOOpenNotifications(118).then(() => {
            cy.sendNotification({data: this.data, hash: this.hash});
        });
    });
});
